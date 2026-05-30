<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Event;
use App\Models\Registration;

class ScopeToClub
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If not logged in, or not a president, skip scoping (or let auth check handle it)
        if (!$user || !$user->isPresident()) {
            return $next($request);
        }

        $userClubId = $user->club_id;

        // 1. Check if route has a 'club' parameter
        $clubParam = $request->route('club');
        if ($clubParam) {
            $clubId = is_object($clubParam) ? $clubParam->id : $clubParam;
            if ($clubId != $userClubId) {
                return $this->forbiddenResponse($request);
            }
        }

        // 2. Check if route has an 'event' parameter
        $eventParam = $request->route('event');
        if ($eventParam) {
            $event = is_object($eventParam) ? $eventParam : Event::find($eventParam);
            if ($event && $event->club_id != $userClubId) {
                return $this->forbiddenResponse($request);
            }
        }

        // 3. Check if route has a 'registration' parameter
        $registrationParam = $request->route('registration');
        if ($registrationParam) {
            $registration = is_object($registrationParam) ? $registrationParam : Registration::with('event')->find($registrationParam);
            if ($registration && $registration->event && $registration->event->club_id != $userClubId) {
                return $this->forbiddenResponse($request);
            }
        }

        return $next($request);
    }

    protected function forbiddenResponse(Request $request): Response
    {
        return $request->expectsJson()
            ? response()->json(['message' => 'Forbidden. This resource belongs to another club.'], 403)
            : abort(403, 'Unauthorized access. This resource belongs to another club.');
    }
}
