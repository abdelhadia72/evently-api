<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Get user's orders
            $orders = Order::where('user_id', Auth::id())
                ->with(['event', 'tickets.ticketType'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $orders,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching orders: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred'],
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            try {
                $validated = $request->validate([
                    'event_id' => 'required|exists:events,id',
                    'tickets' => 'required|array|min:1',
                    'tickets.*.ticket_type_id' => 'required|exists:ticket_types,id',
                    'tickets.*.quantity' => 'required|integer|min:1',
                    'payment_method' => 'required|string',
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Invalid request data: '.$e->getMessage()],
                ], 400);
            }

            try {
                $event = Event::findOrFail($validated['event_id']);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Event not found'],
                ], 404);
            }

            $totalAmount = 0;
            $ticketsToCreate = [];

            foreach ($validated['tickets'] as $ticketRequest) {
                try {
                    $ticketType = TicketType::findOrFail($ticketRequest['ticket_type_id']);

                    if ($ticketType->event_id != $event->id) {
                        DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'errors' => ['Ticket type ID '.$ticketType->id.' does not belong to this event'],
                        ], 400);
                    }

                    if (! $ticketType->is_active) {
                        DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'errors' => ["Ticket type '{$ticketType->name}' is no longer available"],
                        ], 400);
                    }

                    if ($ticketType->availableQuantity() < $ticketRequest['quantity']) {
                        DB::rollBack();

                        return response()->json([
                            'success' => false,
                            'errors' => ["Not enough '{$ticketType->name}' tickets available - requested: ".
                                        $ticketRequest['quantity'].', available: '.$ticketType->availableQuantity()],
                        ], 400);
                    }

                    $ticketPrice = $ticketType->price;
                    $ticketQuantity = $ticketRequest['quantity'];
                    $totalAmount += $ticketPrice * $ticketQuantity;

                    for ($i = 0; $i < $ticketQuantity; $i++) {
                        $ticketsToCreate[] = [
                            'ticket_type_id' => $ticketType->id,
                            'price_paid' => $ticketPrice,
                        ];
                    }
                } catch (\Exception $e) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'errors' => ['Error processing ticket type: '.$e->getMessage()],
                    ], 500);
                }
            }

            try {
                $order = Order::create([
                    'user_id' => Auth::id(),
                    'event_id' => $event->id,
                    'order_number' => 'ORD-'.strtoupper(uniqid()),
                    'total_amount' => $totalAmount,
                    'status' => 'completed',
                    'payment_method' => $validated['payment_method'],
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'errors' => ['Failed to create order: '.$e->getMessage()],
                ], 500);
            }

            try {
                foreach ($ticketsToCreate as $ticketData) {
                    Ticket::create([
                        'event_id' => $event->id,
                        'user_id' => Auth::id(),
                        'order_id' => $order->id,
                        'ticket_type_id' => $ticketData['ticket_type_id'],
                        'price_paid' => $ticketData['price_paid'],
                        'status' => 'active',
                        'ticket_number' => 'TIX-'.strtoupper(uniqid()),
                        'qr_code' => 'QR-'.uniqid(),
                    ]);
                }
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'errors' => ['Failed to create tickets: '.$e->getMessage()],
                ], 500);
            }

            DB::commit();

            $order->load(['tickets.ticketType', 'event']);

            return response()->json([
                'success' => true,
                'message' => 'Order completed successfully',
                'data' => $order,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => ['Unexpected error during order processing: '.$e->getMessage()],
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $order = Order::with(['tickets.ticketType', 'event'])
                ->findOrFail($id);

            if (Auth::id() !== $order->user_id && ! Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'errors' => ['You do not have permission to view this order'],
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching order: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred'],
            ], 500);
        }
    }

    public function cancel($id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($id);

            if (Auth::id() !== $order->user_id && ! Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'errors' => ['You do not have permission to cancel this order'],
                ], 403);
            }

            if ($order->status === 'cancelled' || $order->status === 'refunded') {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'errors' => ['This order has already been cancelled or refunded'],
                ], 400);
            }

            $order->status = 'cancelled';
            $order->save();

            foreach ($order->tickets as $ticket) {
                $ticket->status = 'cancelled';
                $ticket->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order->fresh()->load(['tickets.ticketType', 'event']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling order: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred'],
            ], 500);
        }
    }
}
