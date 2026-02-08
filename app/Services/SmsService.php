<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $baseUrl = 'https://api.smsonlinegh.com/v5';
    protected ?string $apiKey;
    protected bool $enabled;

    public function __construct()
    {
        $this->apiKey = config('services.sms.key');
        $this->enabled = config('services.sms.enabled', false);
    }

    /**
     * Send a non-personalised SMS.
     *
     * @param array|string $destinations Array of phone numbers or single phone number
     * @param string $text Message content
     * @param string $sender Sender ID (must be approved in portal)
     * @return array Response data or error
     */
    public function send(array|string $destinations, string $text, string $sender = 'ElectionsHQ'): array
    {
        $destinations = (array) $destinations;

        // "Safe Mode" check
        if (!$this->enabled) {
            Log::info('SMS Safe Mode: Message would be sent', [
                'to' => $destinations,
                'text' => $text,
                'sender' => $sender,
            ]);

            return [
                'handshake' => ['id' => 0, 'label' => 'HSHK_OK_SAFE_MODE'],
                'data' => [
                    'status' => 'simulated',
                    'message' => 'SMS not sent (Safe Mode enabled)',
                ]
            ];
        }

        if (empty($this->apiKey)) {
            Log::error('SMS Service: API Key not configured');
            return ['error' => 'SMS configuration missing'];
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'key ' . $this->apiKey,
        ])->post("{$this->baseUrl}/message/sms/send", [
            'text' => $text,
            'type' => 0, // GSM Default
            'sender' => $sender,
            'destinations' => $destinations,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('SMS Service Failed', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return ['error' => 'Failed to send SMS', 'details' => $response->body()];
    }

    /**
     * Check account balance.
     */
    public function getBalance(): array
    {
        if (!$this->enabled) {
             return [
                'handshake' => ['id' => 0, 'label' => 'HSHK_OK_SAFE_MODE'],
                'data' => [
                    'balance' => 9999, // Fake balance
                    'model' => 'simulated',
                ]
            ];
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'key ' . $this->apiKey,
        ])->post("{$this->baseUrl}/account/balance");

        return $response->json();
    }
}
