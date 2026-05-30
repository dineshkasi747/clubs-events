<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::with(['club:id,name,logo', 'images'])
            ->where('status', 'active');

        // Filter by club
        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        // Search in title/description/venue
        if ($request->has('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        // Date filters (upcoming/past)
        if ($request->input('type') === 'past') {
            $query->where('end_time', '<', now())->orderBy('start_time', 'desc');
        } else {
            // Default to upcoming
            $query->where('end_time', '>=', now())->orderBy('start_time', 'asc');
        }

        $events = $query->get()->map(function ($event) {
            $event->spots_remaining = $event->spotsRemaining();
            $event->is_full = $event->isFull();
            return $event;
        });

        return response()->json($events);
    }

    public function show($id): JsonResponse
    {
        $event = Event::with(['club:id,name,description,logo', 'images'])->find($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Format model properties
        $event->spots_remaining = $event->spotsRemaining();
        $event->is_full = $event->isFull();

        return response()->json($event);
    }
}
