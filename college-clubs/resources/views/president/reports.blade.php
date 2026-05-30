@extends('layouts.app')

@section('title', 'Analytics & Reports - President Dashboard')

@section('content')
<div class="space-y-8">
    @if($noClub)
        <!-- Warning Card for unassigned presidents -->
        <div class="rounded-2xl border border-amber-500/20 bg-amber-500/10 p-6 text-center space-y-4 max-w-xl mx-auto my-12 backdrop-blur-md">
            <h2 class="text-xl font-bold text-white">No Club Assigned</h2>
            <p class="text-sm text-slate-400">Please contact the administrator to assign you a club to generate reports.</p>
        </div>
    @else
        <!-- Header -->
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white">Analytics & Event Reports</h1>
                <p class="text-slate-400 text-sm mt-1">Extract monthly registration flows, ticket proceeds, capacity checks, and performance indicators.</p>
            </div>
            
            <!-- Filters -->
            <form action="{{ route('president.reports.index') }}" method="GET" class="flex items-center gap-3 bg-slate-900/60 p-2 rounded-xl border border-slate-800">
                <label for="year" class="text-xs uppercase font-semibold tracking-wider text-slate-400 px-2">Report Year</label>
                <select name="year" id="year" onchange="this.form.submit()"
                    class="rounded-lg bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-200 focus:border-brand-500 outline-none px-3 py-1.5 cursor-pointer">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }} Calendar</option>
                    @endfor
                </select>
            </form>
        </div>

        <!-- Top Statistical Highlights -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <!-- Cumulative Registrations -->
            <div class="glass rounded-2xl p-6 relative overflow-hidden">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Selected Year Registrations</span>
                <span class="block text-4xl font-extrabold text-white mt-2">{{ $totalRegistrations }}</span>
                <span class="block text-xs text-slate-500 mt-1">Registrations in calendar year {{ $year }}</span>
            </div>

            <!-- Cumulative Revenue -->
            <div class="glass rounded-2xl p-6 relative overflow-hidden">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Selected Year Ticket Revenue</span>
                <span class="block text-4xl font-extrabold text-emerald-400 mt-2">${{ number_format($totalRevenue, 2) }}</span>
                <span class="block text-xs text-slate-500 mt-1">Gross paid ticket proceeds for {{ $year }}</span>
            </div>

            <!-- Average Revenue / registration -->
            <div class="glass rounded-2xl p-6 relative overflow-hidden">
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Average ticket spend</span>
                <span class="block text-4xl font-extrabold text-indigo-400 mt-2">
                    ${{ $totalRegistrations > 0 ? number_format($totalRevenue / $totalRegistrations, 2) : '0.00' }}
                </span>
                <span class="block text-xs text-slate-500 mt-1">Revenue divided by signup volume</span>
            </div>
        </div>

        <!-- Monthly Trends Chart and Table -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Table listing (1/3 width) -->
            <div class="glass rounded-2xl p-6 space-y-6">
                <h3 class="text-xl font-bold text-white tracking-tight">Monthly Performance</h3>
                
                <div class="overflow-y-auto max-h-96">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 font-semibold uppercase tracking-wider">
                                <th class="pb-2">Month</th>
                                <th class="pb-2 text-center">Registrations</th>
                                <th class="pb-2 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 text-sm">
                            @foreach($monthlyStats as $stat)
                                <tr class="{{ $stat['registrations'] > 0 ? 'bg-indigo-500/5' : '' }}">
                                    <td class="py-2.5 font-medium text-slate-200">{{ $stat['month_name'] }}</td>
                                    <td class="py-2.5 text-center text-slate-300">{{ $stat['registrations'] }}</td>
                                    <td class="py-2.5 text-right font-bold text-emerald-400">${{ number_format($stat['revenue'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Interactive visual chart (2/3 width) -->
            <div class="glass rounded-2xl p-6 lg:col-span-2 space-y-6 flex flex-col justify-between">
                <h3 class="text-xl font-bold text-white tracking-tight">Monthly Registration & Revenue Distributions</h3>

                <!-- CSS Visual Bar Chart -->
                <div class="space-y-4 flex-grow flex flex-col justify-center">
                    @foreach($monthlyStats as $stat)
                        @if($stat['registrations'] > 0 || $stat['revenue'] > 0)
                            <div class="space-y-1">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-semibold text-slate-300">{{ $stat['month_name'] }}</span>
                                    <span class="text-slate-400">
                                        {{ $stat['registrations'] }} Signups &bull; 
                                        <span class="text-emerald-400 font-bold">${{ number_format($stat['revenue'], 2) }}</span>
                                    </span>
                                </div>
                                <div class="w-full h-3 rounded-full bg-slate-900 overflow-hidden flex">
                                    <!-- Signups proportion -->
                                    <div class="bg-indigo-500 h-full rounded-full transition-all duration-500" 
                                         style="width: {{ $totalRegistrations > 0 ? ($stat['registrations'] / $totalRegistrations) * 100 : 0 }}%">
                                    </div>
                                    <!-- Revenue proportion -->
                                    <div class="bg-emerald-500 h-full rounded-full transition-all duration-500" 
                                         style="width: {{ $totalRevenue > 0 ? ($stat['revenue'] / $totalRevenue) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    
                    @if($totalRegistrations === 0 && $totalRevenue === 0)
                        <div class="text-center py-12 text-slate-400 italic text-sm">
                            No monthly records received. Update filters or register test accounts to populate values.
                        </div>
                    @endif
                </div>
                
                <div class="flex gap-4 text-xs font-semibold text-slate-400 mt-2 justify-end border-t border-slate-800 pt-4">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Signups Volume</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Ticket Sales</span>
                </div>
            </div>
        </div>

        <!-- Event-by-Event Breakdown Matrix -->
        <div class="glass rounded-2xl p-6 space-y-6">
            <h3 class="text-xl font-bold text-white tracking-tight">Scaffold Performance Grid</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th class="pb-3">Event Details</th>
                            <th class="pb-3">Event Date</th>
                            <th class="pb-3 text-center">Ticket Cost</th>
                            <th class="pb-3 text-center">Enrollment Ratio</th>
                            <th class="pb-3 text-right">Gross proceeds</th>
                            <th class="pb-3 text-right">Listing state</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40 text-sm">
                        @forelse($eventBreakdown as $event)
                            <tr>
                                <td class="py-3 font-semibold text-white">{{ $event['title'] }}</td>
                                <td class="py-3 text-slate-300 text-xs">{{ $event['formatted_date'] }}</td>
                                <td class="py-3 text-center font-bold text-slate-300">
                                    {{ $event['price'] > 0 ? '$' . number_format($event['price'], 2) : 'Free' }}
                                </td>
                                <td class="py-3 text-center">
                                    <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300">
                                        {{ $event['registrations_count'] }} / {{ $event['capacity'] }}
                                        ({{ $event['capacity'] > 0 ? round(($event['registrations_count'] / $event['capacity']) * 100) : 0 }}%)
                                    </span>
                                </td>
                                <td class="py-3 text-right font-bold text-emerald-400">
                                    ${{ number_format($event['revenue'], 2) }}
                                </td>
                                <td class="py-3 text-right">
                                    @if($event['status'] === 'active')
                                        <span class="inline-flex rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 text-xs font-semibold text-emerald-400">Active</span>
                                    @elseif($event['status'] === 'draft')
                                        <span class="inline-flex rounded-full bg-slate-500/10 border border-slate-500/20 px-2 py-0.5 text-xs font-semibold text-slate-400">Draft</span>
                                    @elseif($event['status'] === 'completed')
                                        <span class="inline-flex rounded-full bg-blue-500/10 border border-blue-500/20 px-2 py-0.5 text-xs font-semibold text-blue-400">Completed</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-500/10 border border-rose-500/20 px-2 py-0.5 text-xs font-semibold text-rose-400">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 italic">No events generated by your club yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
