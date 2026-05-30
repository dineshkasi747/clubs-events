<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title', 'College Clubs & Events Portal'); ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f4ff',
                            100: '#d9e2ff',
                            500: '#4f46e5',
                            600: '#4338ca',
                            700: '#3730a3',
                            900: '#1e1b4b',
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .glass {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .text-glow {
            text-shadow: 0 0 12px rgba(99, 102, 241, 0.4);
        }
        .glow-border:focus {
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
            border-color: rgba(99, 102, 241, 0.5);
        }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body class="h-full flex flex-col font-sans selection:bg-brand-500 selection:text-white antialiased">

    <!-- Header Navigation -->
    <header class="glass sticky top-0 z-40 w-full border-b border-slate-800 backdrop-blur-md">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-400 font-extrabold text-white text-lg shadow-lg shadow-brand-500/20">
                        C
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight text-white">College<span class="text-indigo-400">Clubs</span></span>
                        <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-medium">Management System</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-6">
                    <?php if(auth()->guard()->check()): ?>
                        <?php if(auth()->user()->isAdmin()): ?>
                            <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-sm font-semibold <?php echo e(request()->routeIs('admin.dashboard') ? 'text-indigo-400' : 'text-slate-300 hover:text-white'); ?> transition-colors">Dashboard</a>
                            <a href="<?php echo e(route('admin.clubs.index')); ?>" class="text-sm font-semibold <?php echo e(request()->routeIs('admin.clubs.index') ? 'text-indigo-400' : 'text-slate-300 hover:text-white'); ?> transition-colors">Club Management</a>
                            <a href="<?php echo e(route('admin.notifications.index')); ?>" class="text-sm font-semibold <?php echo e(request()->routeIs('admin.notifications.*') ? 'text-indigo-400' : 'text-slate-300 hover:text-white'); ?> transition-colors">Broadcast Alert</a>
                        <?php endif; ?>

                        <?php if(auth()->user()->isPresident()): ?>
                            <a href="<?php echo e(route('president.dashboard')); ?>" class="text-sm font-semibold <?php echo e(request()->routeIs('president.dashboard') ? 'text-indigo-400' : 'text-slate-300 hover:text-white'); ?> transition-colors">Club Hub</a>
                            <a href="<?php echo e(route('president.events.index')); ?>" class="text-sm font-semibold <?php echo e(request()->routeIs('president.events.*') ? 'text-indigo-400' : 'text-slate-300 hover:text-white'); ?> transition-colors">Events Portal</a>
                            <a href="<?php echo e(route('president.notifications.index')); ?>" class="text-sm font-semibold <?php echo e(request()->routeIs('president.notifications.*') ? 'text-indigo-400' : 'text-slate-300 hover:text-white'); ?> transition-colors">Broadcast Alert</a>
                            <a href="<?php echo e(route('president.reports.index')); ?>" class="text-sm font-semibold <?php echo e(request()->routeIs('president.reports.*') ? 'text-indigo-400' : 'text-slate-300 hover:text-white'); ?> transition-colors">Analytics Reports</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </nav>

                <!-- Profile and Logout -->
                <div class="flex items-center gap-4">
                    <?php if(auth()->guard()->check()): ?>
                        <div class="hidden sm:block text-right">
                            <span class="block text-sm font-semibold text-white"><?php echo e(auth()->user()->name); ?></span>
                            <span class="block text-[10px] text-slate-400">
                                <?php if(auth()->user()->isAdmin()): ?>
                                    System Administrator
                                <?php elseif(auth()->user()->isPresident() && auth()->user()->club): ?>
                                    President, <?php echo e(auth()->user()->club->name); ?>

                                <?php else: ?>
                                    President Account
                                <?php endif; ?>
                            </span>
                            <a href="<?php echo e(auth()->user()->isAdmin() ? route('admin.password.form') : route('president.password.form')); ?>" class="block text-[10px] text-indigo-400 hover:text-indigo-300 underline font-medium mt-0.5">Change Password</a>
                        </div>
                        
                        <div class="h-9 w-[1px] bg-slate-800"></div>

                        <form action="<?php echo e(route('logout')); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="rounded-lg bg-slate-900 border border-slate-800 hover:border-red-500/30 hover:bg-red-500/10 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:text-red-400 transition-all duration-200">
                                Sign Out
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="rounded-lg bg-brand-500 px-3.5 py-1.5 text-sm font-semibold text-white shadow-md shadow-brand-500/20 hover:bg-brand-600 transition-all duration-200">
                            Sign In
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            
            <!-- Toast alerts -->
            <?php if(session('success')): ?>
                <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-emerald-400 shadow-lg backdrop-blur-md">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold"><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="mb-6 flex items-center gap-3 rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-rose-400 shadow-lg backdrop-blur-md">
                    <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="text-sm font-semibold"><?php echo e(session('error')); ?></span>
                </div>
            <?php endif; ?>

            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-900 bg-slate-950/60 py-6 text-center text-xs text-slate-500">
        <div class="mx-auto max-w-7xl px-4">
            <p>&copy; <?php echo e(date('Y')); ?> College Clubs & Events Management. All rights reserved.</p>
        </div>
    </footer>

    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH E:\clubs-events\college-clubs\resources\views/layouts/app.blade.php ENDPATH**/ ?>