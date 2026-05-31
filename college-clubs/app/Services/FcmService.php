<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send push notification to a single device token.
     */
    public static function sendNotification(?string $token, string $title, string $body, array $data = []): bool
    {
        $displayToken = empty($token) ? "No active device token (SIMULATOR FALLBACK)" : $token;

        $projectId = env('FCM_PROJECT_ID');
        $serviceAccountJson = env('FCM_SERVICE_ACCOUNT_JSON');

        // High visibility fallback logging for development / staging testing
        Log::channel('single')->info("\n" . str_repeat('=', 50) . 
            "\n🔥 [FCM PUSH NOTIFICATION SIMULATOR] 🔥" .
            "\n📱 Device Token: {$displayToken}" .
            "\n📣 Title: {$title}" .
            "\n💬 Message: {$body}" .
            "\n📦 Payload: " . json_encode($data, JSON_PRETTY_PRINT) . 
            "\n" . str_repeat('=', 50)
        );

        if (empty($token)) {
            return true; // Successfully logged simulated notification
        }

        $resolvedPath = file_exists($serviceAccountJson) ? $serviceAccountJson : base_path($serviceAccountJson);

        // If credentials exist, execute authentic HTTP API v1 call
        if ($projectId && file_exists($resolvedPath)) {
            try {
                // Fetch OAuth token
                $oauthToken = self::getGoogleAccessToken($resolvedPath);
                
                $messagePayload = [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'channel_id' => 'high_importance_channel',
                            'sound' => 'default',
                            'default_sound' => true,
                            'default_vibrate_timings' => true,
                        ]
                    ]
                ];

                if (!empty($data)) {
                    $messagePayload['data'] = array_map('strval', $data);
                }

                $response = Http::withToken($oauthToken)
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => $messagePayload
                    ]);

                if (!$response->successful()) {
                    Log::error("🔴 FCM Dispatch Failed with status " . $response->status() . ": " . $response->body());
                } else {
                    Log::info("🟢 FCM Dispatch Succeeded!");
                }

                return $response->successful();
            } catch (\Exception $e) {
                Log::error("FCM API Dispatch Error: " . $e->getMessage());
                return false;
            }
        }

        return true; // Return true representing successful log simulation
    }

    /**
     * Broadcast notification to all registered students.
     */
    public static function broadcastToStudents(string $title, string $body, array $data = []): int
    {
        $students = User::where('role', 'student')->get();
        $dispatchedCount = 0;

        foreach ($students as $student) {
            $studentData = array_merge($data, ['recipient_name' => $student->name]);
            if (self::sendNotification($student->fcm_token, $title, $body, $studentData)) {
                $dispatchedCount++;
            }
        }

        return $dispatchedCount;
    }

    /**
     * Retrieve Google OAuth Access Token using service account JSON credentials.
     */
    protected static function getGoogleAccessToken(string $jsonFilePath): string
    {
        $json = json_decode(file_get_contents($jsonFilePath), true);
        $privateKey = $json['private_key'];
        $clientEmail = $json['client_email'];
        
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode($payload);

        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $base64UrlSignature = self::base64UrlEncode($signature);

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]);

        return $response->json()['access_token'] ?? '';
    }

    protected static function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
