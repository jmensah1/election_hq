<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
        $this->baseUrl = config('services.paystack.payment_url');
    }

    /**
     * Initialize a Paystack transaction.
     *
     * @param string $email Customer email
     * @param int $amountInPesewas Amount in pesewas (100 pesewas = 1 GHS)
     * @param string $reference Unique transaction reference
     * @param string $callbackUrl URL to redirect after payment
     * @param array $metadata Additional metadata to attach
     * @return array|null
     */
    public function initializeTransaction(
        string $email,
        int $amountInPesewas,
        string $reference,
        string $callbackUrl,
        array $metadata = []
    ): ?array {
        try {
            $response = Http::withToken($this->secretKey)
                ->post("{$this->baseUrl}/transaction/initialize", [
                    'email' => $email,
                    'amount' => $amountInPesewas,
                    'reference' => $reference,
                    'callback_url' => $callbackUrl,
                    'metadata' => $metadata,
                    'currency' => 'GHS',
                ]);

            if ($response->successful() && $response->json('status')) {
                return $response->json('data');
            }

            Log::error('Paystack initialization failed', [
                'response' => $response->json(),
                'reference' => $reference,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Paystack initialization exception', [
                'error' => $e->getMessage(),
                'reference' => $reference,
            ]);

            return null;
        }
    }

    /**
     * Verify a Paystack transaction.
     *
     * @param string $reference Transaction reference
     * @return array|null
     */
    public function verifyTransaction(string $reference): ?array
    {
        try {
            $response = Http::withToken($this->secretKey)
                ->get("{$this->baseUrl}/transaction/verify/{$reference}");

            if ($response->successful() && $response->json('status')) {
                return $response->json('data');
            }

            Log::error('Paystack verification failed', [
                'response' => $response->json(),
                'reference' => $reference,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Paystack verification exception', [
                'error' => $e->getMessage(),
                'reference' => $reference,
            ]);

            return null;
        }
    }

    /**
     * Validate Paystack webhook signature.
     *
     * @param string $payload Raw request body
     * @param string $signature Paystack signature header
     * @return bool
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $computedSignature = hash_hmac('sha512', $payload, $this->secretKey);
        return hash_equals($computedSignature, $signature);
    }

    /**
     * Generate a unique payment reference.
     *
     * @param int $leadId
     * @return string
     */
    public static function generateReference(int $leadId): string
    {
        return 'EHQ-' . $leadId . '-' . time() . '-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
