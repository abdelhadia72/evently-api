<?php

namespace App\Http\Controllers;

use App\Enums\EventCategory;
use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function createOne(Request $request)
    {
        try {
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

            $category = $request->input('category');
            $status = $request->input('status');

            if (! in_array($category, EventCategory::values())) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'category' => 'Invalid category. Available categories: '.implode(', ', EventCategory::values()),
                    ],
                ], 422);
            }

            if (! in_array($status, EventStatus::values())) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'status' => 'Invalid status. Available statuses: '.implode(', ', EventStatus::values()),
                    ],
                ], 422);
            }

            $validated = $request->validate((new Event)->rules());

            $event = DB::transaction(function () use ($validated) {
                return Event::create([...$validated, 'organizer_id' => Auth::id()]);
            });

            return response()->json(
                [
                    'success' => true,
                    'data' => $event,
                ],
                201
            );

        } catch (\Exception $e) {
            \Log::error('Event creation error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating event',
            ], 500);
        }
    }

    public function readAll(Request $request)
    {
        $query = Event::query();
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
            'message' => 'Event Deleted Successfully',
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

    public function search(Request $request)
    {
        try {
            $query = Event::query();

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('search')) {
                $searchTerm = '%'.$request->search.'%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm);
                });
            }

            $events = $query->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $events->items(),
                    'total' => $events->total(),
                    'current_page' => $events->currentPage(),
                    'per_page' => $events->perPage(),
                    'last_page' => $events->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Search error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error searching events',
            ], 500);
        }
    }
}
