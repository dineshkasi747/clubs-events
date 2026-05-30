<?php $__env->startSection('title', 'Admin Control Panel - College Clubs & Events'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-8">
    <!-- Dashboard Header -->
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">System Administration</h1>
            <p class="text-slate-400 text-sm mt-1">Real-time college network, active clubs, registrations, and transaction streams.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('admin.clubs.index')); ?>" class="rounded-xl bg-gradient-to-r from-brand-600 to-indigo-500 hover:from-brand-500 hover:to-indigo-400 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-500/10 transition-all duration-200">
                Manage Clubs & Presidents
            </a>
        </div>
    </div>

    <!-- Numerical Analytics Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
        <!-- Stat Item: Clubs -->
        <div class="glass rounded-2xl p-6 relative overflow-hidden">
            <div class="absolute right-3 top-3 h-10 w-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
            </div>
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Clubs</span>
            <span class="block text-3xl font-extrabold text-white mt-2"><?php echo e($stats['total_clubs']); ?></span>
        </div>

        <!-- Stat Item: Presidents -->
        <div class="glass rounded-2xl p-6 relative overflow-hidden">
            <div class="absolute right-3 top-3 h-10 w-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
            </div>
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Presidents</span>
            <span class="block text-3xl font-extrabold text-white mt-2"><?php echo e($stats['total_presidents']); ?></span>
        </div>

        <!-- Stat Item: Events -->
        <div class="glass rounded-2xl p-6 relative overflow-hidden">
            <div class="absolute right-3 top-3 h-10 w-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            </div>
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Events</span>
            <span class="block text-3xl font-extrabold text-white mt-2"><?php echo e($stats['total_events']); ?></span>
        </div>

        <!-- Stat Item: Registrations -->
        <div class="glass rounded-2xl p-6 relative overflow-hidden">
            <div class="absolute right-3 top-3 h-10 w-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
            </div>
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Registrations</span>
            <span class="block text-3xl font-extrabold text-white mt-2"><?php echo e($stats['total_registrations']); ?></span>
        </div>

        <!-- Stat Item: Revenue -->
        <div class="glass rounded-2xl p-6 relative overflow-hidden">
            <div class="absolute right-3 top-3 h-10 w-10 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1M4 6h16" /></svg>
            </div>
            <span class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Total Revenue</span>
            <span class="block text-3xl font-extrabold text-emerald-400 mt-2">$<?php echo e(number_format($stats['total_revenue'], 2)); ?></span>
        </div>
    </div>

    <!-- Main Content Section: Club Breakdown and Recent Actions -->
    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Clubs Directory (2/3 width on wide displays) -->
        <div class="glass rounded-2xl p-6 lg:col-span-2 space-y-6">
            <h3 class="text-xl font-bold text-white tracking-tight">Active Clubs Directory</h3>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-400">
                            <th class="pb-3">Club Details</th>
                            <th class="pb-3">President</th>
                            <th class="pb-3 text-center">Events Held</th>
                            <th class="pb-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-sm">
                        <?php $__empty_1 = true; $__currentLoopData = $clubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400 font-extrabold">
                                            <?php echo e(strtoupper(substr($club->name, 0, 2))); ?>

                                        </div>
                                        <div>
                                            <span class="block font-semibold text-white"><?php echo e($club->name); ?></span>
                                            <span class="block text-xs text-slate-400 truncate max-w-xs"><?php echo e($club->description); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4">
                                    <?php if($club->president): ?>
                                        <span class="block font-medium text-slate-200"><?php echo e($club->president->name); ?></span>
                                        <span class="block text-xs text-slate-500"><?php echo e($club->president->email); ?></span>
                                    <?php else: ?>
                                        <span class="text-xs italic text-rose-400">No president assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 text-center font-bold text-slate-300">
                                    <?php echo e($club->events_count); ?>

                                </td>
                                <td class="py-4 text-right">
                                    <form action="<?php echo e(route('admin.clubs.destroy', $club)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this club? All linked events will be removed.')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="rounded bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 hover:border-rose-500/40 text-xs font-bold text-rose-400 px-2 py-1 transition-all duration-200">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400 italic">No clubs are currently active in the system.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Registrations (1/3 width) -->
        <div class="glass rounded-2xl p-6 space-y-6">
            <h3 class="text-xl font-bold text-white tracking-tight">Recent Registrations</h3>
            
            <div class="flow-root">
                <ul class="-my-5 divide-y divide-slate-800/50">
                    <?php $__empty_1 = true; $__currentLoopData = $recentRegistrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center text-xs font-bold text-indigo-400">
                                        <?php echo e(strtoupper(substr($reg->user->name, 0, 2))); ?>

                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-white">
                                        <?php echo e($reg->user->name); ?>

                                    </p>
                                    <p class="truncate text-xs text-slate-400">
                                        Registered for <span class="text-indigo-300"><?php echo e($reg->event->title); ?></span>
                                    </p>
                                    <span class="inline-block text-[10px] text-slate-500 mt-1">
                                        <?php echo e($reg->created_at->diffForHumans()); ?>

                                    </span>
                                </div>
                                <div>
                                    <?php if($reg->payment && $reg->payment->status === 'completed'): ?>
                                        <span class="inline-flex rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-xs font-medium text-emerald-400">
                                            Paid
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex rounded-full bg-blue-500/10 border border-blue-500/20 px-2.5 py-0.5 text-xs font-medium text-blue-400">
                                            Free
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <li class="py-8 text-center text-slate-400 italic text-sm">No recent registrations received.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\clubs-events\college-clubs\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>