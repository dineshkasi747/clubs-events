<?php $__env->startSection('title', 'Manage Events - President Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight text-white">Events Console</h1>
            <p class="text-slate-400 text-sm mt-1">Configure draft/live listings, handle capacities, ticket fees, and student enrollment.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('president.events.create')); ?>" class="rounded-xl bg-gradient-to-r from-brand-600 to-indigo-500 hover:from-brand-500 hover:to-indigo-400 px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all duration-200">
                + Onboard New Event
            </a>
        </div>
    </div>

    <!-- PDF Ingestion Card -->
    <div class="glass rounded-2xl p-6 border border-slate-800 bg-slate-900/30">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <h2 class="text-xl font-bold text-white tracking-tight">Bulk-Import Year-wise Historical Events</h2>
                <p class="text-xs text-slate-400 max-w-2xl">Upload your tenure's activity report in PDF format. The system will automatically parse past events, descriptions, dates, and corresponding images!</p>
            </div>
            
            <form action="<?php echo e(route('president.events.upload-pdf')); ?>" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                <?php echo csrf_field(); ?>
                <div class="relative w-full sm:w-64">
                    <input type="file" name="pdf" id="pdf_file" accept=".pdf" required
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div class="w-full bg-slate-900/60 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-400 flex items-center justify-between hover:border-slate-700 transition-all select-none">
                        <span id="file_name_label" class="truncate">Select Activity PDF...</span>
                        <svg class="h-4 w-4 text-indigo-400 flex-shrink-0 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                    </div>
                </div>
                <button type="submit" class="w-full sm:w-auto rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 px-4 py-2.5 text-xs font-semibold text-white shadow-md transition-all duration-200 flex items-center justify-center gap-2 flex-shrink-0">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    Parse & Seed Events
                </button>
            </form>
        </div>
    </div>

    <!-- Events Grid -->
    <div class="glass rounded-2xl p-6 space-y-6">
        <h3 class="text-xl font-bold text-white tracking-tight">Active & Historical Events</h3>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="rounded-xl border border-slate-800 bg-slate-900/40 overflow-hidden hover:border-slate-700/60 transition-all duration-200 flex flex-col justify-between">
                    <!-- Image placeholder or active event card decoration -->
                    <div class="h-32 bg-slate-800 flex items-center justify-center relative select-none">
                        <?php if($evt->images->isNotEmpty()): ?>
                            <img src="<?php echo e($evt->images->first()->path); ?>" class="h-full w-full object-cover" alt="<?php echo e($evt->title); ?>">
                        <?php else: ?>
                            <div class="text-slate-500 text-center p-4">
                                <svg class="h-8 w-8 mx-auto mb-1 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span class="text-xs">No media uploaded</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Status Badge -->
                        <div class="absolute top-3 right-3">
                            <?php if($evt->status === 'active'): ?>
                                <span class="inline-flex rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-xs font-semibold text-emerald-400 backdrop-blur-md">Active</span>
                            <?php elseif($evt->status === 'draft'): ?>
                                <span class="inline-flex rounded-full bg-slate-500/10 border border-slate-500/20 px-2.5 py-0.5 text-xs font-semibold text-slate-400 backdrop-blur-md">Draft</span>
                            <?php elseif($evt->status === 'completed'): ?>
                                <span class="inline-flex rounded-full bg-blue-500/10 border border-blue-500/20 px-2.5 py-0.5 text-xs font-semibold text-blue-400 backdrop-blur-md">Completed</span>
                            <?php else: ?>
                                <span class="inline-flex rounded-full bg-rose-500/10 border border-rose-500/20 px-2.5 py-0.5 text-xs font-semibold text-rose-400 backdrop-blur-md">Cancelled</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-5 space-y-4 flex-grow flex flex-col justify-between">
                        <div class="space-y-2">
                            <h4 class="font-bold text-white text-lg leading-tight line-clamp-1"><?php echo e($evt->title); ?></h4>
                            <p class="text-xs text-slate-400 line-clamp-2"><?php echo e($evt->description ?? 'No event description entered.'); ?></p>
                        </div>

                        <div class="space-y-3 pt-3 border-t border-slate-800/80">
                            <!-- Date info -->
                            <div class="flex items-center gap-2 text-xs text-slate-300">
                                <svg class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                <span><?php echo e($evt->formatted_date); ?></span>
                            </div>

                            <!-- Venue info -->
                            <div class="flex items-center gap-2 text-xs text-slate-300">
                                <svg class="h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span class="truncate"><?php echo e($evt->venue); ?></span>
                            </div>

                            <!-- Price and Capacity metrics -->
                            <div class="flex justify-between items-center text-xs">
                                <div>
                                    <span class="block text-[9px] uppercase tracking-wider text-slate-500 font-semibold">TICKET PRICE</span>
                                    <span class="font-bold text-white"><?php echo e($evt->price > 0 ? '$' . number_format($evt->price, 2) : 'Free'); ?></span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[9px] uppercase tracking-wider text-slate-500 font-semibold">REGISTRATIONS</span>
                                    <span class="font-bold text-slate-300"><?php echo e($evt->registrations()->where('status', '!=', 'cancelled')->count()); ?> / <?php echo e($evt->capacity); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Actions -->
                        <div class="pt-4 border-t border-slate-800/80 flex items-center justify-between gap-3">
                            <a href="<?php echo e(route('president.events.edit', $evt)); ?>"
                                class="flex-grow text-center rounded-xl bg-slate-800 border border-slate-700/80 hover:bg-slate-700/80 text-xs font-semibold text-slate-200 py-2 transition-all">
                                Edit Event
                            </a>
                            <form action="<?php echo e(route('president.events.destroy', $evt)); ?>" method="POST" class="flex-shrink-0" onsubmit="return confirm('Delete event permanently?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                    class="rounded-xl bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 hover:border-rose-500/40 p-2 text-rose-400 transition-all">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-3 py-16 text-center text-slate-400 bg-slate-900/15 rounded-2xl italic text-sm">
                    Your club has not recorded any events. Click "Onboard New Event" in the header to create one!
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.getElementById('pdf_file').addEventListener('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : "Select Activity PDF...";
        document.getElementById('file_name_label').textContent = fileName;
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\clubs-events\college-clubs\resources\views/president/events/index.blade.php ENDPATH**/ ?>