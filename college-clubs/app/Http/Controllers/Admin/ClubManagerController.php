<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ClubManagerController extends Controller
{
    public function index(): View
    {
        $clubs = Club::with('president')->orderBy('name')->get();
        
        // Find presidents who don't have a club assigned yet
        $unassignedPresidents = User::where('role', 'president')
            ->whereNull('club_id')
            ->get();

        return view('admin.clubs', compact('clubs', 'unassignedPresidents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:clubs,name'],
            'description' => ['nullable', 'string'],
            'president_name' => ['required_if:president_id,new', 'string', 'max:255'],
            'president_email' => ['required_if:president_id,new', 'nullable', 'email', 'unique:users,email'],
            'president_id' => ['required', 'string'],
        ]);

        $presidentId = $request->input('president_id');

        // Create a new president user if chosen
        if ($presidentId === 'new') {
            $president = User::create([
                'name' => $request->input('president_name'),
                'email' => $request->input('president_email'),
                'password' => Hash::make('password'), // default password
                'role' => 'president',
            ]);
            $presidentId = $president->id;
        }

        // Create the club
        $club = Club::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'president_id' => $presidentId,
            'logo' => '/storage/logos/default_club.png',
        ]);

        // Link the president to this club
        $presidentUser = User::find($presidentId);
        if ($presidentUser) {
            $presidentUser->update(['club_id' => $club->id]);
        }

        return redirect()->route('admin.clubs.index')->with('success', "Club '{$club->name}' has been created successfully.");
    }

    public function destroy(Club $club): RedirectResponse
    {
        $clubName = $club->name;
        
        // Unlink president user first
        if ($club->president_id) {
            User::where('id', $club->president_id)->update(['club_id' => null]);
        }

        $club->delete();

        return redirect()->route('admin.clubs.index')->with('success', "Club '{$clubName}' has been deleted successfully.");
    }
}
