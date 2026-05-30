<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - College Clubs & Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f4ff',
                            500: '#4f46e5',
                            600: '#4338ca',
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
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-sphere {
            box-shadow: 0 0 120px 40px rgba(99, 102, 241, 0.15);
        }
    </style>
</head>
<body class="h-full flex items-center justify-center font-sans relative overflow-hidden antialiased select-none">

    <!-- Decorative background elements -->
    <div class="absolute top-1/4 left-1/4 h-80 w-80 rounded-full glow-sphere bg-indigo-600/10 blur-3xl -z-10"></div>
    <div class="absolute bottom-1/4 right-1/4 h-96 w-96 rounded-full glow-sphere bg-purple-600/10 blur-3xl -z-10"></div>

    <div class="w-full max-w-md p-6">
        <div class="glass rounded-3xl p-8 shadow-2xl relative overflow-hidden">
            <!-- Glass gloss highlight -->
            <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>

            <div class="text-center mb-8">
                <!-- Glowing Shield Icon -->
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-2xl bg-gradient-to-tr from-brand-600 to-indigo-400 text-white font-extrabold text-2xl shadow-xl shadow-indigo-500/20 mb-4">
                    C
                </div>
                <h2 class="text-3xl font-extrabold tracking-tight text-white">Welcome Back</h2>
                <p class="text-sm text-slate-400 mt-1">Sign in to your administration dashboard</p>
            </div>

            <!-- Login Form -->
            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Email Address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition-all duration-200"
                        placeholder="president1@college.edu">
                    @error('email')
                        <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">Password</label>
                    </div>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition-all duration-200"
                        placeholder="••••••••">
                    @error('password')
                        <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 rounded border-slate-800 bg-slate-900 text-brand-600 focus:ring-brand-500/20 outline-none cursor-pointer">
                    <label for="remember" class="ml-2.5 text-xs text-slate-400 font-medium cursor-pointer">Keep me signed in on this computer</label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                        class="w-full rounded-xl bg-gradient-to-r from-brand-600 to-indigo-500 py-3.5 text-sm font-bold text-white shadow-xl shadow-brand-500/10 hover:shadow-brand-500/25 hover:from-brand-500 hover:to-indigo-400 focus:ring-2 focus:ring-brand-500/30 outline-none transition-all duration-300">
                        Sign In Portal
                    </button>
                </div>
            </form>
            
            <!-- Quick Logins Panel for Demo purposes -->
            <div class="mt-8 pt-6 border-t border-slate-800 text-center">
                <span class="text-[10px] uppercase font-semibold tracking-widest text-slate-500 block mb-3">Quick Access Logins</span>
                <div class="flex flex-col gap-1.5 text-xs font-medium text-slate-400">
                    <div>
                        <span class="text-indigo-400">Admin:</span> admin@college.edu / password
                    </div>
                    <div>
                        <span class="text-indigo-400">President:</span> president1@college.edu / password
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
