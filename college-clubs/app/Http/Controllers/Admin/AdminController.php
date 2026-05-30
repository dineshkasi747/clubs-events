<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Payment;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'total_clubs' => Club::count(),
            'total_presidents' => User::where('role', 'president')->count(),
            'total_events' => Event::count(),
            'total_registrations' => Registration::count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
        ];

        // Recent registrations
        $recentRegistrations = Registration::with(['user', 'event.club', 'payment'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Club breakdown list
        $clubs = Club::withCount(['events'])
            ->with(['president'])
            ->get();

        return view('admin.dashboard', compact('stats', 'recentRegistrations', 'clubs'));
    }

    public function showNotificationsForm(): \Illuminate\View\View
    {
        return view('admin.notifications');
    }

    public function sendBroadcastNotification(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:250'],
        ]);

        $dispatched = \App\Services\FcmService::broadcastToStudents(
            $request->title,
            $request->body,
            ['type' => 'broadcast', 'sent_by' => 'Admin']
        );

        return redirect()->back()->with('success', "Custom notification broadcast dispatched successfully to {$dispatched} student devices!");
    }
}
