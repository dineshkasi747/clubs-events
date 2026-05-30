@extends('layouts.app')

@section('title', 'Notification Broadcast Center - Admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-8">
    <!-- Header Section -->
    <div class="text-center space-y-2">
        <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-400 mb-2">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">System Notification Center</h1>
        <p class="text-slate-400 text-sm max-w-lg mx-auto">Compose and dispatch custom, real-time Firebase Push Notifications directly to all registered student mobile devices.</p>
    </div>

    <!-- Main Glassmorphic Form Card -->
    <div class="glass rounded-3xl p-8 relative overflow-hidden">
        <form action="{{ route('admin.notifications.send') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Notification Title Field -->
            <div class="space-y-2">
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Notification Header / Title
                </label>
                <div class="relative rounded-xl shadow-sm">
                    <input 
                        type="text" 
                        name="title" 
                        id="title" 
                        required 
                        maxlength="100"
                        value="{{ old('title', '🔥 New Club Event is Coming!') }}"
                        placeholder="e.g. 🔥 New Club Event is Coming!" 
                        class="block w-full rounded-xl border-slate-800 bg-slate-900/60 py-3 px-4 text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border glow-border"
                    >
                </div>
                @error('title')
                    <p class="text-xs text-red-400 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notification Body Field -->
            <div class="space-y-2">
                <label for="body" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Message Body / Content
                </label>
                <div class="relative rounded-xl shadow-sm">
                    <textarea 
                        name="body" 
                        id="body" 
                        rows="4" 
                        required 
                        maxlength="250"
                        placeholder="Provide details about the upcoming event, registration link, or announcement here..." 
                        class="block w-full rounded-xl border-slate-800 bg-slate-900/60 py-3 px-4 text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border glow-border"
                    >{{ old('body') }}</textarea>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-500">
                    <span>Keep descriptions brief and action-oriented for better click rates.</span>
                    <span id="char-count">0 / 250</span>
                </div>
                @error('body')
                    <p class="text-xs text-red-400 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Informational Banner -->
            <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/5 p-4 text-indigo-400 flex gap-3 text-xs leading-5">
                <svg class="h-5 w-5 flex-shrink-0 text-indigo-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <span class="font-bold block mb-0.5 text-white">Universal Dispatch Mode</span>
                    This broadcast is delivered to **all registered students** globally across both Android and iOS devices. The background payload will set key metadata indicating an administrative dispatch.
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-2">
                <button 
                    type="submit" 
                    class="w-full rounded-xl bg-gradient-to-r from-brand-600 to-indigo-500 hover:from-brand-500 hover:to-indigo-400 py-3 px-4 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition-all duration-200 flex items-center justify-center gap-2 hover:shadow-brand-500/30"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    Dispatch Push Notification
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const textarea = document.getElementById('body');
    const charCount = document.getElementById('char-count');
    
    textarea.addEventListener('input', function() {
        const count = textarea.value.length;
        charCount.textContent = `${count} / 250`;
    });
</script>
@endsection
