<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $registrations = Registration::with(['event.club', 'event.images', 'payment'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($registrations);
    }

    public function register(Request $request, $eventId): JsonResponse
    {
        $user = $request->user();
        $event = Event::find($eventId);

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        if ($event->status !== 'active') {
            return response()->json(['message' => 'This event is not accepting registrations.'], 400);
        }

        // Check if student is already registered
        $existing = Registration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You are already registered for this event.',
                'registration' => $existing
            ], 400);
        }

        // Check capacity
        if ($event->isFull()) {
            return response()->json(['message' => 'This event is at full capacity.'], 400);
        }

        // Start Transaction
        DB::beginTransaction();
        try {
            // Create Registration
            $registration = Registration::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'status' => $event->price > 0 ? 'pending' : 'approved',
            ]);

            // If event is paid, simulate payment creation
            $payment = null;
            if ($event->price > 0) {
                $request->validate([
                    'payment_method' => ['required', 'string', 'in:stripe,paypal,card'],
                ]);

                // Create a simulated completed payment
                $payment = Payment::create([
                    'registration_id' => $registration->id,
                    'amount' => $event->price,
                    'payment_method' => $request->payment_method,
                    'status' => 'completed', // auto-complete for testing/mocking
                    'transaction_id' => 'tx_' . Str::random(16),
                ]);

                // Approve the registration now that it is paid
                $registration->update(['status' => 'approved']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Successfully registered for event.',
                'registration' => $registration->load('payment'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
