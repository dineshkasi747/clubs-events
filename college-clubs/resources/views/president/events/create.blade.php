@extends('layouts.app')

@section('title', ($event ? 'Edit Event' : 'Onboard Event') . ' - President Dashboard')

@section('content')
<div class="max-w-3xl mx-auto space-y-8">
    <!-- Breadcrumbs/Back -->
    <div>
        <a href="{{ route('president.events.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition flex items-center gap-1.5">
            &larr; Back to Events Portal
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white">
            {{ $event ? "Update '{$event->title}'" : 'Create New Club Event' }}
        </h1>
        <p class="text-slate-400 text-sm mt-1">Configure schedule parameters, fees, limits, and banner media uploads.</p>
    </div>

    <!-- Form Panel -->
    <div class="glass rounded-2xl p-8 relative overflow-hidden">
        <!-- Glass top boundary sheen -->
        <div class="absolute top-0 left-0 right-0 h-[1px] bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>

        <form action="{{ $event ? route('president.events.update', $event) : route('president.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if($event)
                @method('PUT')
            @endif

            <!-- Event Title -->
            <div>
                <label for="title" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Event Title</label>
                <input type="text" id="title" name="title" required value="{{ old('title', $event ? $event->title : '') }}"
                    class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition"
                    placeholder="e.g., Spring Technical Summit">
                @error('title')
                    <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Event Description</label>
                <textarea id="description" name="description" rows="4"
                    class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition"
                    placeholder="Provide a compelling description of activities, benefits, schedules...">{{ old('description', $event ? $event->description : '') }}</textarea>
                @error('description')
                    <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Grid: Venue and Price/Capacity -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Venue -->
                <div>
                    <label for="venue" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Event Venue</label>
                    <input type="text" id="venue" name="venue" required value="{{ old('venue', $event ? $event->venue : '') }}"
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white placeholder-slate-500 outline-none transition"
                        placeholder="e.g., Campus Center Ballroom">
                    @error('venue')
                        <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Listing Status</label>
                    <select id="status" name="status" required
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white outline-none transition cursor-pointer">
                        <option value="active" {{ old('status', $event ? $event->status : '') === 'active' ? 'selected' : '' }}>Active / Live</option>
                        <option value="draft" {{ old('status', $event ? $event->status : '') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="completed" {{ old('status', $event ? $event->status : '') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $event ? $event->status : '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Grid: Price and Capacity -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Price -->
                <div>
                    <label for="price" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Ticket price ($)</label>
                    <input type="number" step="0.01" min="0" id="price" name="price" required value="{{ old('price', $event ? $event->price : '0.00') }}"
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white outline-none transition"
                        placeholder="0.00">
                    <p class="text-[10px] text-slate-500 mt-1.5">Enter 0 for free events.</p>
                    @error('price')
                        <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Max Capacity</label>
                    <input type="number" min="1" id="capacity" name="capacity" required value="{{ old('capacity', $event ? $event->capacity : '100') }}"
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white outline-none transition"
                        placeholder="100">
                    <p class="text-[10px] text-slate-500 mt-1.5">Limits maximum student signups.</p>
                    @error('capacity')
                        <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Grid: Dates -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Start Time -->
                <div>
                    <label for="start_time" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Start Date & Time</label>
                    <input type="datetime-local" id="start_time" name="start_time" required
                        value="{{ old('start_time', ($event && $event->start_time) ? $event->start_time->format('Y-m-d\TH:i') : '') }}"
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white outline-none transition cursor-pointer">
                    @error('start_time')
                        <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Time -->
                <div>
                    <label for="end_time" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">End Date & Time</label>
                    <input type="datetime-local" id="end_time" name="end_time" required
                        value="{{ old('end_time', ($event && $event->end_time) ? $event->end_time->format('Y-m-d\TH:i') : '') }}"
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 px-4 py-3 text-sm text-white outline-none transition cursor-pointer">
                    @error('end_time')
                        <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Image File Upload -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Event Image Banner</label>
                
                @if($event && $event->images->isNotEmpty())
                    <div class="mb-3 flex items-center gap-3 p-3 rounded-xl border border-slate-800 bg-slate-900/60">
                        <img src="{{ $event->images->first()->path }}" class="h-12 w-16 object-cover rounded-lg" alt="Thumbnail">
                        <div class="text-xs">
                            <span class="block text-slate-300 font-semibold">Active Banner</span>
                            <span class="block text-slate-500 truncate max-w-xs">{{ $event->images->first()->path }}</span>
                        </div>
                    </div>
                @endif

                <div class="relative w-full rounded-xl bg-slate-900 border border-dashed border-slate-800 focus-within:border-brand-500 hover:border-slate-700 transition px-4 py-5 flex flex-col items-center cursor-pointer">
                    <svg class="h-8 w-8 text-slate-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="text-xs text-slate-400 font-semibold">Drag image files or <span class="text-indigo-400 underline">browse computer</span></span>
                    <span class="text-[10px] text-slate-500 mt-1">Supports PNG, JPG, JPEG up to 2MB.</span>
                    <input type="file" id="image" name="image" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>
                @error('image')
                    <p class="text-xs text-rose-500 mt-2 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions Submit -->
            <div class="pt-6 border-t border-slate-800 flex items-center justify-end gap-4">
                <a href="{{ route('president.events.index') }}"
                    class="rounded-xl border border-slate-850 hover:bg-slate-900 px-5 py-3 text-sm font-semibold text-slate-300 hover:text-white transition">
                    Cancel Config
                </a>
                <button type="submit"
                    class="rounded-xl bg-gradient-to-r from-brand-600 to-indigo-500 hover:from-brand-500 hover:to-indigo-400 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-brand-500/10 transition-all duration-200">
                    {{ $event ? 'Save Event Config' : 'Publish & Scaffold Event' }}
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
