<?php $__env->startSection('title', 'President Club Hub - College Clubs & Events'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-8">
    <?php if($noClub): ?>
        <!-- Warning Card for unassigned presidents -->
        <div class="rounded-2xl border border-amber-500/20 bg-amber-500/10 p-6 text-center space-y-4 max-w-xl mx-auto my-12 backdrop-blur-md">
            <div class="mx-auto h-12 w-12 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white">No Club Assigned</h2>
            <p class="text-sm text-slate-400">
                Your president account is currently active, but the System Administrator hasn't linked you to a specific club yet.
                Please contact the college administration to link your club so you can manage events and see statistics.
            </p>
            <div class="pt-2">
                <a href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="rounded-xl border border-slate-700 bg-slate-900 px-4 py-2 text-xs font-semibold text-slate-300 hover:text-white transition">
                    Sign Out
                </a>
                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="hidden"><?php echo csrf_field(); ?></form>
            </div>
        </div>
    <?php else: ?>
        <!-- Dashboard Header -->
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    <?php echo e($club->name); ?> <span class="text-indigo-400">Dashboard</span>
                </h1>
                <p class="text-slate-400 text-sm mt-1">Welcome back, President. Monitor events, registrations, and club revenue.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('president.events.create')); ?>" class="rounded-xl bg-gradient-to-r from-brand-600 to-indigo-500 hover:from-brand-500 hover:to-indigo-400 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/10 transition-all duration-200">
                    + Onboard New Event
                </a>
            </div>
        </div>

        <!-- Numerical Analytics Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Events -->
            <div class="glass rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute right-3 top-3 h-10 w-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Events Scaffolds</span>
                <span class="block text-3xl font-extrabold text-white mt-2"><?php echo e($stats['total_events']); ?></span>
            </div>

            <!-- Active Events -->
            <div class="glass rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute right-3 top-3 h-10 w-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Active Live Events</span>
                <span class="block text-3xl font-extrabold text-indigo-400 mt-2"><?php echo e($stats['active_events']); ?></span>
            </div>

            <!-- Registrations -->
            <div class="glass rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute right-3 top-3 h-10 w-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Signups</span>
                <span class="block text-3xl font-extrabold text-white mt-2"><?php echo e($stats['total_registrations']); ?></span>
            </div>

            <!-- Club Revenue -->
            <div class="glass rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute right-3 top-3 h-10 w-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1M4 6h16" /></svg>
                </div>
                <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Club Revenue</span>
                <span class="block text-3xl font-extrabold text-emerald-400 mt-2">$<?php echo e(number_format($stats['total_revenue'], 2)); ?></span>
            </div>
        </div>

        <!-- Upcoming and Recent Registrations -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
            <!-- Upcoming Events -->
            <div class="glass rounded-2xl p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white tracking-tight">Upcoming Events</h3>
                    <a href="<?php echo e(route('president.events.index')); ?>" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">View All Events &rarr;</a>
                </div>

                <div class="space-y-4">
                    <?php $__empty_1 = true; $__currentLoopData = $upcomingEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between p-4 bg-slate-900/50 rounded-xl border border-slate-800/80">
                            <div>
                                <span class="block font-semibold text-white text-sm"><?php echo e($evt->title); ?></span>
                                <span class="block text-xs text-slate-400 mt-1">
                                    <?php echo e($evt->formatted_date); ?> &bull; <?php echo e($evt->venue); ?>

                                </span>
                            </div>
                            <div class="text-right">
                                <span class="block text-xs font-bold text-indigo-400">
                                    <?php echo e($evt->price > 0 ? '$' . number_format($evt->price, 2) : 'FREE'); ?>

                                </span>
                                <span class="block text-[10px] text-slate-500 mt-0.5">
                                    <?php echo e($evt->spotsRemaining()); ?> spots left
                                </span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-slate-400 italic text-sm py-6 text-center">No upcoming events scheduled. Click "Onboard New Event" to get started!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Event Registrations -->
            <div class="glass rounded-2xl p-6 space-y-6">
                <h3 class="text-xl font-bold text-white tracking-tight">Recent Registrations</h3>

                <div class="flow-root">
                    <ul class="-my-4 divide-y divide-slate-800/50">
                        <?php $__empty_1 = true; $__currentLoopData = $recentRegistrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <li class="py-4">
                                <div class="flex items-center justify-between text-sm">
                                    <div>
                                        <span class="block font-semibold text-white"><?php echo e($reg->user->name); ?></span>
                                        <span class="block text-xs text-slate-400">Registered for <span class="text-indigo-400"><?php echo e($reg->event->title); ?></span></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="block text-xs text-slate-500"><?php echo e($reg->created_at->diffForHumans()); ?></span>
                                        <?php if($reg->payment && $reg->payment->status === 'completed'): ?>
                                            <span class="inline-block text-[10px] text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded-full mt-0.5">
                                                Paid $<?php echo e(number_format($reg->payment->amount, 2)); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="inline-block text-[10px] text-indigo-400 font-semibold bg-indigo-500/10 px-2 py-0.5 rounded-full mt-0.5">
                                                Free
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-slate-400 italic text-sm py-6 text-center">No registrations recorded for your events yet.</p>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\clubs-events\college-clubs\resources\views/president/dashboard.blade.php ENDPATH**/ ?>