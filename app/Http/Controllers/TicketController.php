<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Notifications\TicketBookedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request, $eventId): JsonResponse
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('viewAny', [Ticket::class, $event]);

        $tickets = $event->tickets()
            ->when(! Auth::user()->hasPermission('tickets', 'read'), function ($query) {
                return $query->where('user_id', Auth::id());
            })
            ->with(['user'])
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    public function store(Request $request, $eventId): JsonResponse
    {
        try {
            $event = Event::findOrFail($eventId);

            if (! Auth::user()->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account must be verified before booking tickets.',
                ], 403);
            }

            if ($event->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This event is not available for booking.',
                ], 403);
            }

            $this->authorize('create', [Ticket::class, $event]);

            $existingTicket = Ticket::where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->whereNotIn('status', ['cancelled'])
                ->first();

            if ($existingTicket) {
                return response()->json([
                    'success' => false,
                    'message' => 'You already have a ticket for this event.',
                    'ticket' => $existingTicket,
                ], 400);
            }

            $ticket = Ticket::create([
                'event_id' => $event->id,
                'user_id' => Auth::id(),
            ]);

            try {
                Auth::user()->notify(new TicketBookedNotification($ticket, $event));
            } catch (\Exception $e) {
                \Log::error('Failed to send ticket notification:', [
                    'error' => $e->getMessage(),
                    'user_id' => Auth::id(),
                    'ticket_id' => $ticket->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ticket booked successfully. Check your email for details.',
                'data' => $ticket->fresh(),
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Ticket creation error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'event_id' => $eventId,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error booking ticket',
            ], 500);
        }
    }

    public function update(Request $request, string $ticketId): JsonResponse
    {
        $ticket = Ticket::findOrFail($ticketId);
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'status' => 'sometimes|string|in:active,cancelled',
        ]);

        $ticket->update($validated);

        return response()->json([
            'success' => true,
            'data' => $ticket->fresh(),
        ]);
    }

    public function destroy(string $eventId): JsonResponse
    {
        try {
            $event = Event::findOrFail($eventId);

            $ticket = Ticket::where('event_id', $event->id)
                ->where('user_id', Auth::id())
                ->where('status', 'active')  // Only cancel active tickets
                ->first();

            if (! $ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active ticket found for this event',
                ], 404);
            }

            $this->authorize('delete', $ticket); // Add policy check

            $ticket->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket cancelled successfully',
            ]);

        } catch (\Exception $e) {
            \Log::error('Ticket cancellation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Add stack trace
                'event_id' => $eventId,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error cancelling ticket',
            ], 500);
        }
    }

    public function checkIn(Request $request, string $ticketId): JsonResponse
    {
        $ticket = Ticket::findOrFail($ticketId);
        $this->authorize('checkIn', $ticket);

        $request->validate([
            'qr_code' => 'required|string',
        ]);

        if ($ticket->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ticket',
            ], 400);
        }

        if (! $ticket->verifyQRCode($request->qr_code)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code',
            ], 400);
        }

        $ticket->update([
            'status' => 'used',
            'check_in_time' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
        ]);
    }

    public function show(string $ticketId): JsonResponse
    {
        $ticket = Ticket::with(['event', 'user'])->findOrFail($ticketId);
        $this->authorize('view', $ticket);

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }
}
