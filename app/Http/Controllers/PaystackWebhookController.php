<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function __construct(
        protected PaystackService $paystackService
    ) {}

    /**
     * Handle incoming Paystack webhooks.
     */
    public function handle(Request $request): Response
    {
        // Validate webhook signature
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (!$this->paystackService->validateWebhookSignature($payload, $signature ?? '')) {
            Log::warning('Invalid Paystack webhook signature', [
                'ip' => $request->ip(),
            ]);
            return response('Invalid signature', 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');

        Log::info('Paystack webhook received', ['event' => $event]);

        match ($event) {
            'charge.success' => $this->handleChargeSuccess($data),
            default => Log::info('Unhandled Paystack event', ['event' => $event]),
        };

        return response('Webhook processed', 200);
    }

    /**
     * Handle successful charge event.
     */
    protected function handleChargeSuccess(array $data): void
    {
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            Log::error('Paystack charge.success missing reference', $data);
            return;
        }

        $payment = Payment::where('reference', $reference)->first();

        if (!$payment) {
            Log::warning('Payment not found for Paystack reference', ['reference' => $reference]);
            return;
        }

        // Idempotency check - don't process if already successful
        if ($payment->isSuccessful()) {
            Log::info('Payment already marked as successful', ['reference' => $reference]);
            return;
        }

        // Mark payment as successful
        $payment->markAsSuccessful(
            paystackReference: $data['reference'],
            channel: $data['channel'] ?? null,
            metadata: $data['metadata'] ?? null
        );

        // Update lead status
        $payment->lead()->update(['status' => 'paid']);

        Log::info('Payment marked as successful via webhook', [
            'reference' => $reference,
            'amount' => $payment->amount,
        ]);
    }
}
