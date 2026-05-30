<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $club = $user->club;

        if (!$club) {
            return view('president.dashboard', [
                'noClub' => true,
                'club' => null,
                'stats' => null,
                'upcomingEvents' => collect(),
                'recentRegistrations' => collect()
            ]);
        }

        // Stats for this club's events
        $eventIds = Event::where('club_id', $club->id)->pluck('id');

        $stats = [
            'total_events' => Event::where('club_id', $club->id)->count(),
            'active_events' => Event::where('club_id', $club->id)->where('status', 'active')->count(),
            'total_registrations' => Registration::whereIn('event_id', $eventIds)->count(),
            'total_revenue' => Payment::whereHas('registration', function ($query) use ($eventIds) {
                $query->whereIn('event_id', $eventIds);
            })->where('status', 'completed')->sum('amount'),
        ];

        // Upcoming events
        $upcomingEvents = Event::where('club_id', $club->id)
            ->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get();

        // Recent registrations
        $recentRegistrations = Registration::with(['user', 'event', 'payment'])
            ->whereIn('event_id', $eventIds)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('president.dashboard', [
            'noClub' => false,
            'club' => $club,
            'stats' => $stats,
            'upcomingEvents' => $upcomingEvents,
            'recentRegistrations' => $recentRegistrations
        ]);
    }

    public function showNotificationsForm(): \Illuminate\View\View
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $club = $user->club;

        if (!$club) {
            abort(403, 'Unauthorized.');
        }

        return view('president.notifications', compact('club'));
    }

    public function sendBroadcastNotification(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $club = $user->club;

        if (!$club) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:250'],
        ]);

        $dispatched = \App\Services\FcmService::broadcastToStudents(
            $request->title,
            $request->body,
            [
                'type' => 'club_broadcast',
                'club_id' => (string)$club->id,
                'club_name' => $club->name,
                'sent_by' => 'President'
            ]
        );

        return redirect()->back()->with('success', "Notification broadcast dispatched successfully to {$dispatched} student devices for {$club->name}!");
    }
}
