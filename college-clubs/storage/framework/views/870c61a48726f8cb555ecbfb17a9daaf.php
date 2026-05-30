<?php $__env->startSection('title', 'Change Password - College Clubs Portal'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto space-y-8">
    <!-- Breadcrumbs/Back -->
    <div>
        <a href="<?php echo e(auth()->user()->isAdmin() ? route('admin.dashboard') : route('president.dashboard')); ?>" 
            class="text-xs font-semibold text-slate-400 hover:text-white transition flex items-center gap-1.5">
            &larr; Back to Dashboard
        </a>
    </div>

    <!-- Header -->
    <div class="text-center">
        <h1 class="text-3xl font-extrabold tracking-tight text-white">Change Account Password</h1>
        <p class="text-slate-400 text-sm mt-1">Configure a secure password for your college club account.</p>
    </div>

    <!-- Form Panel -->
    <div class="glass rounded-3xl p-8 relative overflow-hidden shadow-2xl">
        <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>

        <form action="<?php echo e(auth()->user()->isAdmin() ? route('admin.password.update') : route('president.password.update')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <!-- Current Password -->
            <div>
                <label for="current_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Current Password</label>
                <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                    class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition"
                    placeholder="••••••••">
                <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-rose-500 mt-2 font-medium"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- New Password -->
            <div>
                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">New Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                    class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition"
                    placeholder="••••••••">
                <p class="text-[10px] text-slate-500 mt-1.5">Must be at least 8 characters long.</p>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-rose-500 mt-2 font-medium"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <!-- Confirm New Password -->
            <div>
                <label for="password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Confirm New Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                    class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition"
                    placeholder="••••••••">
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-brand-600 to-indigo-500 hover:from-brand-500 hover:to-indigo-400 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-500/10 transition-all duration-200">
                    Update Account Password
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\clubs-events\college-clubs\resources\views/auth/password.blade.php ENDPATH**/ ?>