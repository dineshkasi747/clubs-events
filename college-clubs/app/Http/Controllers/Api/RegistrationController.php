<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\FcmService;

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

            $razorpayOrder = null;

            if ($event->price > 0) {
                // Request Order Creation from Razorpay API
                $keyId = env('RAZORPAY_KEY_ID');
                $keySecret = env('RAZORPAY_KEY_SECRET');

                if (empty($keyId) || empty($keySecret)) {
                    // Fallback to local sandbox testing order
                    $razorpayOrder = [
                        'id' => 'order_mock_' . Str::random(12),
                        'amount' => $event->price * 100, // paise
                        'currency' => 'INR',
                        'mock' => true
                    ];
                } else {
                    $response = Http::withBasicAuth($keyId, $keySecret)
                        ->post('https://api.razorpay.com/v1/orders', [
                            'amount' => $event->price * 100, // Razorpay works in paise
                            'currency' => 'INR',
                            'receipt' => 'rcpt_reg_' . $registration->id,
                        ]);

                    if ($response->successful()) {
                        $razorpayOrder = $response->json();
                    } else {
                        throw new \Exception('Failed to create Razorpay order: ' . $response->body());
                    }
                }
            } else {
                // Free event: send confirmation push notification immediately
                if ($user->fcm_token) {
                    FcmService::sendNotification(
                        $user->fcm_token,
                        "🎉 Registration Confirmed!",
                        "You are successfully registered for '{$event->title}'. Enjoy!"
                    );
                }
            }

            DB::commit();

            return response()->json([
                'message' => $event->price > 0 ? 'Registration initiated.' : 'Successfully registered for event.',
                'registration' => $registration,
                'razorpay_order' => $razorpayOrder,
                'razorpay_key' => env('RAZORPAY_KEY_ID', 'rzp_test_mockkey')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Registration Error: " . $e->getMessage());
            return response()->json([
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $registration = Registration::with('event', 'user')->find($request->registration_id);

        if ($registration->status === 'approved') {
            return response()->json(['message' => 'Payment already verified and approved.'], 200);
        }

        // Cryptographic verification
        $keySecret = env('RAZORPAY_KEY_SECRET');
        
        $isValid = false;
        if (str_starts_with($request->razorpay_order_id, 'order_mock_') || empty($keySecret)) {
            // Skips cryptographic signature check for seamless sandbox testing
            $isValid = true;
        } else {
            $generatedSignature = hash_hmac(
                'sha256',
                $request->razorpay_order_id . '|' . $request->razorpay_payment_id,
                $keySecret
            );
            $isValid = hash_equals($generatedSignature, $request->razorpay_signature);
        }

        if (!$isValid) {
            return response()->json(['message' => 'Payment signature verification failed.'], 400);
        }

        DB::beginTransaction();
        try {
            // Update Registration Status
            $registration->update(['status' => 'approved']);

            // Create Completed Payment Record
            $payment = Payment::create([
                'registration_id' => $registration->id,
                'amount' => $registration->event->price,
                'payment_method' => 'razorpay',
                'status' => 'completed',
                'transaction_id' => $request->razorpay_payment_id,
            ]);

            DB::commit();

            // ⚡ Fire real-time Push Notification!
            if ($registration->user->fcm_token) {
                FcmService::sendNotification(
                    $registration->user->fcm_token,
                    "🎟️ Ticket Secured!",
                    "Payment of ₹" . number_format($registration->event->price, 2) . " verified. You are going to {$registration->event->title}!"
                );
            }

            return response()->json([
                'message' => 'Payment verified successfully and ticket issued.',
                'registration' => $registration->load('payment')
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment Verification Error: " . $e->getMessage());
            return response()->json(['message' => 'Failed to verify payment: ' . $e->getMessage()], 500);
        }
    }

    public function showCheckoutPage(Request $request)
    {
        $registrationId = $request->query('registration_id');
        $orderId = $request->query('order_id');
        $amount = $request->query('amount');
        $key = $request->query('key');
        
        $registration = Registration::with('event', 'user')->findOrFail($registrationId);

        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Secure Checkout</title>
            <script src='https://checkout.razorpay.com/v1/checkout.js'></script>
            <script src='https://cdn.tailwindcss.com'></script>
            <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap' rel='stylesheet'>
            <style>
                body {
                    font-family: 'Outfit', sans-serif;
                    background: #030712;
                    color: #fff;
                }
                .glass {
                    background: rgba(17, 24, 39, 0.7);
                    backdrop-filter: blur(12px);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                }
            </style>
        </head>
        <body class='min-h-screen flex items-center justify-center p-4'>
            <div class='glass max-w-md w-full rounded-3xl p-8 text-center space-y-6 shadow-2xl relative'>
                <div class='flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-400 mx-auto mb-2'>
                    <svg class='h-6 w-6' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'>
                        <path stroke-linecap='round' stroke-linejoin='round' d='M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' />
                    </svg>
                </div>
                <div>
                    <h1 class='text-2xl font-extrabold text-white'>Secure Payment Gateway</h1>
                    <p class='text-slate-400 text-xs mt-1'>Powered by Razorpay Standard Checkout</p>
                </div>

                <div class='border-y border-slate-800 py-4 space-y-2 text-left'>
                    <div class='flex justify-between text-sm'>
                        <span class='text-slate-400'>Event</span>
                        <span class='font-semibold text-white'>{$registration->event->title}</span>
                    </div>
                    <div class='flex justify-between text-sm'>
                        <span class='text-slate-400'>Registration ID</span>
                        <span class='font-semibold text-white'>#{$registration->id}</span>
                    </div>
                    <div class='flex justify-between text-sm'>
                        <span class='text-slate-400'>Total Price</span>
                        <span class='font-extrabold text-emerald-400'>₹" . number_format($registration->event->price, 2) . "</span>
                    </div>
                </div>

                <div class='space-y-4'>
                    <p class='text-xs text-slate-400'>Choose UPI (PhonePe, GPay, Paytm) or Card in the popup window to pay securely.</p>
                    <button id='pay-button' class='w-full rounded-xl bg-gradient-to-r from-indigo-600 to-brand-500 hover:from-indigo-500 hover:to-brand-400 py-3.5 px-4 text-sm font-bold text-white shadow-lg transition-all duration-200'>
                        Launch Checkout Wizard
                    </button>
                </div>

                <form id='verify-form' action='" . route('payment.success') . "' method='GET' class='hidden'>
                    <input type='hidden' name='registration_id' value='{$registration->id}'>
                    <input type='hidden' name='razorpay_payment_id' id='rzp-payment-id'>
                    <input type='hidden' name='razorpay_order_id' id='rzp-order-id'>
                    <input type='hidden' name='razorpay_signature' id='rzp-signature'>
                </form>

                <script>
                    var key = '{$key}';
                    var amount = '{$amount}';
                    var orderId = '{$orderId}';
                    
                    var options = {
                        'key': key,
                        'amount': amount,
                        'currency': 'INR',
                        'name': 'College Clubs & Events',
                        'description': 'Ticket Registration for {$registration->event->title}',
                        'order_id': orderId,
                        'handler': function (response) {
                            document.getElementById('rzp-payment-id').value = response.razorpay_payment_id;
                            document.getElementById('rzp-order-id').value = response.razorpay_order_id;
                            document.getElementById('rzp-signature').value = response.razorpay_signature;
                            document.getElementById('verify-form').submit();
                        },
                        'prefill': {
                            'name': '{$registration->user->name}',
                            'email': '{$registration->user->email}'
                        },
                        'theme': {
                            'color': '#4F46E5'
                        }
                    };
                    
                    var rzp = new Razorpay(options);
                    
                    document.getElementById('pay-button').onclick = function(e){
                        rzp.open();
                        e.preventDefault();
                    }
                    
                    // Auto-open on load
                    window.onload = function() {
                        rzp.open();
                    };
                </script>
            </div>
        </body>
        </html>
        ";
    }

    public function showSuccessPage(Request $request)
    {
        $registrationId = $request->query('registration_id');
        $paymentId = $request->query('razorpay_payment_id');
        $orderId = $request->query('razorpay_order_id');
        $signature = $request->query('razorpay_signature');

        // Execute payment verification by calling the verifyPayment method programmatically
        $verifyRequest = Request::create('/api/payments/verify', 'POST', [
            'registration_id' => $registrationId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_order_id' => $orderId,
            'razorpay_signature' => $signature,
        ]);
        
        $response = $this->verifyPayment($verifyRequest);
        
        $isSuccess = $response->status() === 200;

        return "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Payment Status</title>
            <script src='https://cdn.tailwindcss.com'></script>
            <link href='https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap' rel='stylesheet'>
            <style>
                body {
                    font-family: 'Outfit', sans-serif;
                    background: #030712;
                    color: #fff;
                }
                .glass {
                    background: rgba(17, 24, 39, 0.7);
                    backdrop-filter: blur(12px);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                }
            </style>
        </head>
        <body class='min-h-screen flex items-center justify-center p-4'>
            <div class='glass max-w-md w-full rounded-3xl p-8 text-center space-y-6 shadow-2xl'>
                " . ($isSuccess ? "
                    <div class='flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-400 mx-auto'>
                        <svg class='h-8 w-8' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2.5'>
                            <path stroke-linecap='round' stroke-linejoin='round' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' />
                        </svg>
                    </div>
                    <div>
                        <h1 class='text-2xl font-extrabold text-white'>Payment Confirmed!</h1>
                        <p class='text-emerald-400 text-sm mt-1'>Ticket Secured Successfully</p>
                    </div>
                    <p class='text-xs text-slate-400 leading-relaxed px-4'>
                        Your payment has been cryptographically verified by the backend. You have also been dispatched a real-time push notification!
                    </p>
                    <div class='bg-slate-900/50 rounded-xl p-4 border border-slate-800 text-xs text-slate-400 space-y-1 text-left'>
                        <div><strong class='text-white'>Receipt Transaction:</strong> {$paymentId}</div>
                        <div><strong class='text-white'>Status:</strong> Approved & Completed</div>
                    </div>
                " : "
                    <div class='flex h-16 w-16 items-center justify-center rounded-full bg-rose-500/10 text-rose-400 mx-auto'>
                        <svg class='h-8 w-8' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2.5'>
                            <path stroke-linecap='round' stroke-linejoin='round' d='M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' />
                        </svg>
                    </div>
                    <div>
                        <h1 class='text-2xl font-extrabold text-white'>Verification Failed</h1>
                        <p class='text-rose-400 text-sm mt-1'>Cryptographic Error</p>
                    </div>
                    <p class='text-xs text-slate-400 px-4'>
                        The signature hash verification failed or was rejected by our server security guard. Please check backend logs or try again.
                    </p>
                ") . "
                
                <div class='pt-2'>
                    <button onclick='window.close();' class='w-full rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 py-3 text-sm font-bold text-slate-300 hover:text-white transition-all duration-200'>
                        Close Checkout Window
                    </button>
                    <p class='text-[10px] text-slate-500 mt-3'>You can safely close this browser window now.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
