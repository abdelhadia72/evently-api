<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function createOne(Request $request)
    {
        if (! Auth::user()->hasPermission('events', 'create')) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => ['You do not have permission to create events'],
                ],
                403
            );
        }

        if (! Auth::user()->is_verified) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => ['You need to verify your account before creating events'],
                ],
                403
            );
        }

        $validated = $request->validate((new Event)->rules());

        $event = Event::create([...$validated, 'organizer_id' => Auth::id()]);

        return response()->json(
            [
                'success' => true,
                'data' => $event,
            ],
            201
        );
    }

    public function readAll(Request $request)
    {
        $query = Event::query();

        if (! Auth::user()->hasPermission('events', 'read')) {
            if (! Auth::user()->hasPermission('events', 'read_own')) {
                return response()->json(
                    [
                        'success' => false,
                        'errors' => ['You do not have permission to view events'],
                    ],
                    403
                );
            }
            $query->where('organizer_id', Auth::id());
        }

        $events = $query->paginate($request->input('per_page', 15));

        $response = $events->toArray();
        $response['items'] = $response['data'];
        unset($response['data']);

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    public function readOne(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $event,
        ]);
    }

    public function updateOne(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if (
            ! Auth::user()->hasPermission('events', 'update') &&
            (! Auth::user()->hasPermission('events', 'update_own') || $event->organizer_id !== Auth::id())
        ) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => ['You do not have permission to update this event'],
                ],
                403
            );
        }

        $validated = $request->validate((new Event)->rules($id));

        $event->update($validated);

        return response()->json([
            'success' => true,
            'data' => $event,
        ]);
    }

    public function deleteOne(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if (
            ! Auth::user()->hasPermission('events', 'delete') &&
            (! Auth::user()->hasPermission('events', 'delete_own') || $event->organizer_id !== Auth::id())
        ) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => ['You do not have permission to delete this event'],
                ],
                403
            );
        }

        $event->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function getAttendees(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $event->tickets()->with('user')->paginate($request->input('per_page', 15)),
        ]);
    }
}
