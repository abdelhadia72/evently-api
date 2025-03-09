<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TicketTypeController extends Controller
{
    public function index(Request $request, $eventId): JsonResponse
    {
        try {
            $event = Event::findOrFail($eventId);

            $ticketTypes = $event->ticketTypes;

            if (! Auth::check() || (Auth::id() !== $event->organizer_id && ! Auth::user()->is_admin)) {
                $ticketTypes = $ticketTypes->where('is_active', true);
            }

            return response()->json([
                'success' => true,
                'data' => $ticketTypes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching ticket types: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred'],
            ], 500);
        }
    }

    public function store(Request $request, $eventId): JsonResponse
    {
        try {
            $event = Event::findOrFail($eventId);

            Log::info('Creating ticket type - request data:', $request->all());

            if (Auth::id() !== $event->organizer_id && ! Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Only the event organizer can create ticket types'],
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'description' => 'nullable|string',
            ]);

            // Add event_id to the validated data
            $validated['event_id'] = $event->id;
            $validated['is_active'] = true;

            Log::info('Ticket type data being saved:', $validated);

            try {
                $ticketType = TicketType::create($validated);
                Log::info('Ticket type created successfully: '.$ticketType->id);

                return response()->json([
                    'success' => true,
                    'data' => $ticketType,
                ], 201);
            } catch (\Exception $innerException) {
                Log::error('Failed during TicketType creation: '.$innerException->getMessage());
                Log::error($innerException->getTraceAsString());

                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create ticket type',
                    'debug' => $innerException->getMessage(),
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error in store method: '.$e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred'],
                'debug' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $ticketType = TicketType::findOrFail($id);
            $event = $ticketType->event;

            // Check permissions
            if (Auth::id() !== $event->organizer_id && ! Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Only the event organizer can update ticket types'],
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'price' => 'sometimes|numeric|min:0',
                'quantity' => 'sometimes|integer|min:0',
                'description' => 'nullable|string',
                'is_active' => 'sometimes|boolean',
            ]);

            $ticketType->update($validated);

            return response()->json([
                'success' => true,
                'data' => $ticketType->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating ticket type: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred'],
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $ticketType = TicketType::findOrFail($id);
            $event = $ticketType->event;

            // Check permissions
            if (Auth::id() !== $event->organizer_id && ! Auth::user()->is_admin) {
                return response()->json([
                    'success' => false,
                    'errors' => ['Only the event organizer can delete ticket types'],
                ], 403);
            }

            // Check if any tickets have been sold
            $soldTickets = $ticketType->tickets()
                ->whereHas('order', function ($query) {
                    $query->whereNotIn('status', ['cancelled', 'refunded']);
                })
                ->count();

            if ($soldTickets > 0) {
                // Don't delete if tickets have been sold, just deactivate
                $ticketType->update(['is_active' => false]);

                return response()->json([
                    'success' => true,
                    'message' => 'Ticket type has been deactivated (tickets already sold)',
                ]);
            }

            $ticketType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ticket type deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting ticket type: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['An unexpected error occurred'],
            ], 500);
        }
    }
}
