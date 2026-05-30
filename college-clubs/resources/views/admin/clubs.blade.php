@extends('layouts.app')

@section('title', 'Manage Clubs - Admin Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white">Clubs & Presidents Manager</h1>
        <p class="text-slate-400 text-sm mt-1">Spin up new college clubs, onboard presidents, and configure network scope control.</p>
    </div>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Clubs Directory List (2/3 width) -->
        <div class="glass rounded-2xl p-6 lg:col-span-2 space-y-6">
            <h3 class="text-xl font-bold text-white tracking-tight">Existing College Clubs</h3>
            
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @forelse($clubs as $club)
                    <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-5 space-y-4 hover:border-slate-700/60 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-lg bg-indigo-500/10 flex items-center justify-center text-indigo-400 font-extrabold text-lg">
                                {{ strtoupper(substr($club->name, 0, 2)) }}
                            </div>
                            <div>
                                <h4 class="font-bold text-white">{{ $club->name }}</h4>
                                <span class="text-xs text-indigo-400">ID: {{ $club->id }}</span>
                            </div>
                        </div>
                        
                        <p class="text-xs text-slate-400 line-clamp-2 h-8">{{ $club->description ?? 'No description provided.' }}</p>
                        
                        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs">
                            <div>
                                <span class="block text-[10px] uppercase font-semibold text-slate-500">Club President</span>
                                @if($club->president)
                                    <span class="font-medium text-slate-300">{{ $club->president->name }}</span>
                                @else
                                    <span class="font-medium text-rose-400 italic">Unassigned</span>
                                @endif
                            </div>
                            
                            <form action="{{ route('admin.clubs.destroy', $club) }}" method="POST" onsubmit="return confirm('Delete {{ $club->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/20 hover:border-rose-500/40 text-xs font-bold text-rose-400 px-2.5 py-1.5 transition-all">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 py-12 text-center text-slate-400 italic bg-slate-900/20 rounded-xl">
                        No clubs configured. Create one using the form on the right!
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Add Club Form (1/3 width) -->
        <div class="glass rounded-2xl p-6 h-fit space-y-6">
            <h3 class="text-xl font-bold text-white tracking-tight">Onboard New Club</h3>
            
            <form action="{{ route('admin.clubs.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Club Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Club Name</label>
                    <input type="text" id="name" name="name" required value="{{ old('name') }}"
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 px-3.5 py-2.5 text-sm text-white outline-none transition"
                        placeholder="e.g., Coding Club">
                    @error('name')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 px-3.5 py-2.5 text-sm text-white outline-none transition"
                        placeholder="Welcome statement and activity summary...">{{ old('description') }}</textarea>
                </div>

                <!-- President Option Selector -->
                <div>
                    <label for="president_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Club President Account</label>
                    <select id="president_id" name="president_id" onchange="togglePresidentFields(this.value)"
                        class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 px-3.5 py-2.5 text-sm text-white outline-none transition cursor-pointer">
                        <option value="new" selected>+ Create New President User</option>
                        @foreach($unassignedPresidents as $pres)
                            <option value="{{ $pres->id }}">{{ $pres->name }} ({{ $pres->email }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- New President User Fields (Toggled) -->
                <div id="new_president_fields" class="space-y-4 pt-2">
                    <div>
                        <label for="president_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">President Name</label>
                        <input type="text" id="president_name" name="president_name" value="{{ old('president_name') }}"
                            class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 px-3.5 py-2.5 text-sm text-white outline-none transition"
                            placeholder="Full Name">
                        @error('president_name')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="president_email" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">President Email</label>
                        <input type="email" id="president_email" name="president_email" value="{{ old('president_email') }}"
                            class="w-full rounded-xl bg-slate-900 border border-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 px-3.5 py-2.5 text-sm text-white outline-none transition"
                            placeholder="president@college.edu">
                        @error('president_email')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full rounded-xl bg-gradient-to-r from-brand-600 to-indigo-500 hover:from-brand-500 hover:to-indigo-400 py-3 text-sm font-bold text-white shadow-lg transition-all duration-200 mt-4">
                    Create Club & Deploy
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePresidentFields(val) {
        const fields = document.getElementById('new_president_fields');
        const nameInput = document.getElementById('president_name');
        const emailInput = document.getElementById('president_email');
        
        if (val === 'new') {
            fields.style.display = 'block';
            nameInput.required = true;
            emailInput.required = true;
        } else {
            fields.style.display = 'none';
            nameInput.required = false;
            emailInput.required = false;
        }
    }
    
    // Initial check
    togglePresidentFields(document.getElementById('president_id').value);
</script>
@endsection
