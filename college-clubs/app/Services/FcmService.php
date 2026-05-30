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
        if (empty($token)) {
            return false;
        }

        $projectId = env('FCM_PROJECT_ID');
        $serviceAccountJson = env('FCM_SERVICE_ACCOUNT_JSON');

        // High visibility fallback logging for development / staging testing
        Log::channel('single')->info("\n" . str_repeat('=', 50) . 
            "\n🔥 [FCM PUSH NOTIFICATION SIMULATOR] 🔥" .
            "\n📱 Device Token: {$token}" .
            "\n📣 Title: {$title}" .
            "\n💬 Message: {$body}" .
            "\n📦 Payload: " . json_encode($data, JSON_PRETTY_PRINT) . 
            "\n" . str_repeat('=', 50)
        );

        // If credentials exist, execute authentic HTTP API v1 call
        if ($projectId && file_exists($serviceAccountJson)) {
            try {
                // Fetch OAuth token
                $oauthToken = self::getGoogleAccessToken($serviceAccountJson);
                
                $response = Http::withToken($oauthToken)
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => array_map('strval', $data), // FCM requires strings in data payload
                        ]
                    ]);

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
        $students = User::where('role', 'student')->whereNotNull('fcm_token')->get();
        $dispatchedCount = 0;

        foreach ($students as $student) {
            if (self::sendNotification($student->fcm_token, $title, $body, $data)) {
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
