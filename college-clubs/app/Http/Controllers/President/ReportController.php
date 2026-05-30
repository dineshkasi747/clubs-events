<?php

namespace App\Http\Controllers\President;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $club = Auth::user()->club;
        if (!$club) {
            return view('president.reports', [
                'noClub' => true,
                'year' => date('Y'),
                'monthlyStats' => [],
                'eventBreakdown' => [],
                'totalRevenue' => 0,
                'totalRegistrations' => 0
            ]);
        }

        $year = $request->input('year', date('Y'));
        $eventIds = Event::where('club_id', $club->id)->pluck('id');

        // 1. Calculate monthly registration count and revenue for selected year using database-agnostic Carbon collections
        $registrations = Registration::whereIn('event_id', $eventIds)
            ->whereYear('created_at', $year)
            ->get();

        $monthlyRegistrations = $registrations->groupBy(function ($reg) {
            return $reg->created_at->format('m');
        })->map(function ($group) {
            return $group->count();
        })->toArray();

        $payments = Payment::join('registrations', 'payments.registration_id', '=', 'registrations.id')
            ->whereIn('registrations.event_id', $eventIds)
            ->whereYear('payments.created_at', $year)
            ->where('payments.status', 'completed')
            ->select('payments.*')
            ->get();

        $monthlyRevenue = $payments->groupBy(function ($payment) {
            return $payment->created_at->format('m');
        })->map(function ($group) {
            return floatval($group->sum('amount'));
        })->toArray();

        // Build monthlyStats array for 12 months
        $monthlyStats = [];
        $months = [
            '01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
            '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
            '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
        ];

        $totalRevenue = 0;
        $totalRegistrations = 0;

        foreach ($months as $num => $name) {
            $regCount = $monthlyRegistrations[$num] ?? 0;
            $revAmount = floatval($monthlyRevenue[$num] ?? 0);
            
            $monthlyStats[] = [
                'month_name' => $name,
                'registrations' => $regCount,
                'revenue' => $revAmount
            ];

            $totalRevenue += $revAmount;
            $totalRegistrations += $regCount;
        }

        // 2. Event-by-event performance breakdown
        $eventBreakdown = Event::where('club_id', $club->id)
            ->withCount(['registrations as active_registrations' => function ($query) {
                $query->where('status', '!=', 'cancelled');
            }])
            ->get()
            ->map(function ($event) {
                $revenue = Payment::whereHas('registration', function ($query) use ($event) {
                    $query->where('event_id', $event->id);
                })->where('status', 'completed')->sum('amount');

                return [
                    'title' => $event->title,
                    'formatted_date' => $event->formatted_date,
                    'price' => $event->price,
                    'capacity' => $event->capacity,
                    'registrations_count' => $event->active_registrations,
                    'revenue' => floatval($revenue),
                    'status' => $event->status,
                ];
            });

        return view('president.reports', [
            'noClub' => false,
            'year' => $year,
            'monthlyStats' => $monthlyStats,
            'eventBreakdown' => $eventBreakdown,
            'totalRevenue' => $totalRevenue,
            'totalRegistrations' => $totalRegistrations
        ]);
    }
}
