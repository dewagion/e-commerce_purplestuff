<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BiteshipService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(trim((string) config('services.biteship.base_url')), '/');
        $this->apiKey = trim((string) config('services.biteship.api_key'));
    }

    public function calculateRatesByPostalCode(
        string $originPostalCode,
        string $destinationPostalCode,
        array $items,
        ?string $couriers = null
    ): array {
        $this->guardConfigured($originPostalCode, $destinationPostalCode, $items);

        $payload = [
            'origin_postal_code' => (int) trim($originPostalCode),
            'destination_postal_code' => (int) trim($destinationPostalCode),
            'couriers' => trim($couriers ?: (string) config('services.biteship.couriers', 'jne')),
            'items' => $items,
        ];

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'authorization' => $this->apiKey,
                'content-type' => 'application/json',
            ])
            ->post($this->baseUrl . '/rates/couriers', $payload);

        $json = $response->json();

        if ($response->failed()) {
            throw new RuntimeException(
                $this->extractMessage($json, 'Gagal menghitung ongkir dari Biteship.')
            );
        }

        $pricing = data_get($json, 'pricing');

        return is_array($pricing) ? $pricing : [];
    }

    protected function guardConfigured(
        string $originPostalCode,
        string $destinationPostalCode,
        array $items
    ): void {
        if (
            $this->baseUrl === '' ||
            $this->apiKey === '' ||
            trim($originPostalCode) === '' ||
            trim($destinationPostalCode) === ''
        ) {
            throw new RuntimeException(
                'Konfigurasi Biteship belum lengkap. Isi BITESHIP_API_KEY, BITESHIP_BASE_URL, dan BITESHIP_ORIGIN_POSTAL_CODE di file .env.'
            );
        }

        if (
            !str_starts_with($this->apiKey, 'biteship_test.') &&
            !str_starts_with($this->apiKey, 'biteship_live.')
        ) {
            throw new RuntimeException(
                'Format BITESHIP_API_KEY tidak valid. Gunakan API key resmi dari dashboard Biteship.'
            );
        }

        if (count($items) === 0) {
            throw new RuntimeException(
                'Item pengiriman kosong. Tidak bisa menghitung ongkir.'
            );
        }

        foreach ($items as $index => $item) {
            $name = (string) ($item['name'] ?? '');
            $value = (int) ($item['value'] ?? 0);
            $quantity = (int) ($item['quantity'] ?? 0);
            $weight = (int) ($item['weight'] ?? 0);

            if ($name === '' || $value <= 0 || $quantity <= 0 || $weight <= 0) {
                throw new RuntimeException(
                    'Data item ke-' . ($index + 1) . ' belum lengkap. Pastikan name, value, quantity, dan weight terisi dengan benar.'
                );
            }
        }
    }

    protected function extractMessage(mixed $json, string $fallback): string
    {
        if (is_array($json)) {
            $message = data_get($json, 'error')
                ?: data_get($json, 'message')
                ?: data_get($json, 'errors.0.message');

            if (is_string($message) && $message !== '') {
                return $message;
            }
        }

        return $fallback;
    }
}