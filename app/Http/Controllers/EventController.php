<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Notifications\EventAdditionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function createOne(Request $request)
    {
        // check for permission
        if (! Auth::user()->hasPermission('events', 'create')) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => ['You do not have permission to create events'],
                ],
                403
            );
        }

        // check for verification
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

        // we add this for items insted of data
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

        // if (
        //   !Auth::user()->hasPermission('events', 'read') &&
        //   (!Auth::user()->hasPermission('events', 'read_own') || $event->organizer_id !== Auth::id())
        // ) {
        //   return response()->json(
        //     [
        //       'success' => false,
        //       'errors' => ['You do not have permission to view this event'],
        //     ],
        //     403
        //   );
        // }

        return response()->json([
            'success' => true,
            'data' => $event,
        ]);
    }

    public function updateOne(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if ($event->organizer_id !== Auth::id()) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => ['You can only update events that you have created'],
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

    public function attend(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        // event is full
        if ($event->max_attendees && $event->attendees()->count() >= $event->max_attendees) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => ['This event has reached maximum capacity'],
                ],
                400
            );
        }

        // already attending the event
        if ($event->attendees()->where('user_id', Auth::id())->exists()) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => ['You have already attend to this event'],
                ],
                400
            );
        }

        // attach the user with their RSVP status
        $event->attendees()->attach(Auth::id(), [
            'status' => $request->input('status', 'attending'),
            'comment' => $request->input('comment'),
        ]);

        // send notification via mail
        $user = Auth::user();
        $user->notify(new EventAdditionNotification($event->title, $event->id, $user->id));

        return response()->json([
            'success' => true,
            'message' => 'Successfully RSVP\'d to event',
        ]);
    }

    public function updateAttendance(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $event->attendees()->updateExistingPivot(Auth::id(), [
            'status' => $request->input('status'),
            'comment' => $request->input('comment'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully updated RSVP status',
        ]);
    }

    public function cancelAttendance($id)
    {
        $event = Event::findOrFail($id);

        $event->attendees()->detach(Auth::id());

        return response()->json([
            'success' => true,
            'message' => 'Successfully cancelled attendance',
        ]);
    }

    public function getAttendees($id)
    {
        $event = Event::findOrFail($id);

        $attendees = $event
            ->attendees()
            ->with(['roles'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendees,
        ]);
    }
}
