<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
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

    public function store(Request $request, Event $event): JsonResponse
    {
        $existingTicket = Ticket::where('event_id', $event->id)
            ->where('user_id', Auth::id())
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

        return response()->json([
            'success' => true,
            'message' => 'Ticket created successfully',
            'ticket' => $ticket,
        ], 201);
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

    public function destroy(Event $event): JsonResponse
    {
        $ticket = Ticket::where('event_id', $event->id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $ticket) {
            return response()->json([
                'success' => false,
                'message' => 'You don\'t have any tickets for this event',
            ], 404);
        }

        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket cancelled successfully',
        ]);
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
