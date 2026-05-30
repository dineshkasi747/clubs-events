<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\JsonResponse;

class ClubController extends Controller
{
    public function index(): JsonResponse
    {
        $clubs = Club::with('president:id,name,email')
            ->withCount('events')
            ->orderBy('name')
            ->get();

        return response()->json($clubs);
    }

    public function show($id): JsonResponse
    {
        $club = Club::with([
            'president:id,name,email',
            'events' => function ($query) {
                $query->with('images')->orderBy('start_time', 'asc');
            }
        ])->find($id);

        if (!$club) {
            return response()->json(['message' => 'Club not found'], 404);
        }

        return response()->json($club);
    }
}
