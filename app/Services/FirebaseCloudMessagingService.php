<?php

namespace App\Services;

use App\Models\AdminPushSubscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class FirebaseCloudMessagingService
{
    public function isConfigured(): bool
    {
        return (bool) (
            config('services.firebase.project_id') &&
            config('services.firebase.client_email') &&
            config('services.firebase.private_key')
        );
    }

    public function webConfig(): array
    {
        return [
            'apiKey' => (string) config('services.firebase.web.api_key'),
            'authDomain' => (string) config('services.firebase.web.auth_domain'),
            'projectId' => (string) config('services.firebase.web.project_id', config('services.firebase.project_id')),
            'storageBucket' => (string) config('services.firebase.web.storage_bucket'),
            'messagingSenderId' => (string) config('services.firebase.web.messaging_sender_id'),
            'appId' => (string) config('services.firebase.web.app_id'),
            'measurementId' => (string) config('services.firebase.web.measurement_id'),
        ];
    }

    public function webPushEnabled(): bool
    {
        $web = $this->webConfig();

        return $this->isConfigured()
            && !empty($web['apiKey'])
            && !empty($web['projectId'])
            && !empty($web['messagingSenderId'])
            && !empty($web['appId'])
            && !empty(config('services.firebase.web.vapid_key'));
    }

    public function sendToSubscriptions(iterable $subscriptions, array $payload): void
    {
        if (!$this->webPushEnabled()) {
            return;
        }

        foreach ($subscriptions as $subscription) {
            if (!$subscription instanceof AdminPushSubscription || !$subscription->fcm_token) {
                continue;
            }

            $result = $this->sendToToken($subscription->fcm_token, $payload);

            if (in_array($result['error_code'] ?? null, ['UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                $subscription->delete();
            }
        }
    }

    public function sendToToken(string $token, array $payload): array
    {
        if (!$this->webPushEnabled()) {
            return ['ok' => false, 'error' => 'firebase_not_configured'];
        }

        $projectId = (string) config('services.firebase.project_id');
        $accessToken = $this->getAccessToken();

        $message = [
            'token' => $token,
            'data' => array_filter([
                'notification_id' => (string) ($payload['notification_id'] ?? ''),
                'link' => (string) ($payload['link'] ?? ''),
                'type' => (string) ($payload['type'] ?? ''),
                'order_number' => (string) ($payload['order_number'] ?? ''),
                'message' => (string) ($payload['body'] ?? ''),
                'title' => (string) ($payload['title'] ?? config('services.firebase.web.default_title', 'Notifikasi Admin')),
                'icon' => (string) ($payload['icon'] ?? '/favicon.ico'),
                'badge' => (string) ($payload['badge'] ?? '/favicon.ico'),
                'tag' => (string) ($payload['notification_id'] ?? Str::uuid()->toString()),
            ], fn ($value) => $value !== ''),
            'webpush' => [
                'headers' => [
                    'Urgency' => 'high',
                    'TTL' => '2419200',
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                'message' => $message,
            ]);

        if ($response->successful()) {
            return ['ok' => true, 'response' => $response->json()];
        }

        $json = $response->json();
        $errorCode = data_get($json, 'error.details.0.errorCode')
            ?? data_get($json, 'error.status');

        return [
            'ok' => false,
            'status' => $response->status(),
            'error_code' => $errorCode,
            'response' => $json,
        ];
    }

    protected function getAccessToken(): string
    {
        $cacheKey = 'firebase_access_token';
        $cached = Cache::get($cacheKey);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $jwt = $this->makeJwt();

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            throw new RuntimeException('Gagal mendapatkan access token Firebase.');
        }

        $token = (string) $response->json('access_token');
        $expiresIn = max(60, ((int) $response->json('expires_in', 3600)) - 120);

        Cache::put($cacheKey, $token, now()->addSeconds($expiresIn));

        return $token;
    }

    protected function makeJwt(): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_UNESCAPED_SLASHES));

        $issuedAt = time();
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => (string) config('services.firebase.client_email'),
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ], JSON_UNESCAPED_SLASHES));

        $unsignedToken = $header . '.' . $payload;
        $privateKey = str_replace("\\n", "\n", (string) config('services.firebase.private_key'));

        $signature = '';
        $ok = openssl_sign($unsignedToken, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$ok) {
            throw new RuntimeException('Gagal menandatangani JWT Firebase.');
        }

        return $unsignedToken . '.' . $this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
