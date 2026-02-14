<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Organization;
use App\Models\Payment;
use App\Models\User;
use App\Mail\NewLeadAdminNotification;
use App\Mail\LeadReceivedConfirmation;
use App\Services\OrganizationSetupService;
use App\Services\PaystackService;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    public function __construct(
        protected PaystackService $paystackService,
        protected OrganizationSetupService $organizationSetupService
    ) {}

    public function create(Request $request)
    {
        $plan = $request->query('plan', 'basic');
        $billing = $request->query('billing', 'monthly');
        return view('onboarding.create', compact('plan', 'billing'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'organization_name' => 'required|string|max:255',
            'plan_tier' => ['required', Rule::in(array_keys(config('pricing.plans', [])))],
            'billing_cycle' => ['required', Rule::in(['monthly', 'annual'])],
            'message' => 'nullable|string|max:1000',
        ]);

        // Create lead
        $lead = Lead::create([
            ...$validated,
            'ip_address' => $request->ip(),
            'status' => 'new',
        ]);

        // Get plan pricing
        $amount = $this->getPlanAmount($validated['plan_tier'], $validated['billing_cycle']);

        // Create pending payment
        $reference = PaystackService::generateReference($lead->id);
        $payment = Payment::create([
            'lead_id' => $lead->id,
            'reference' => $reference,
            'amount' => $amount,
            'currency' => 'GHS',
            'status' => 'pending',
        ]);

        // Initialize Paystack transaction
        $paystackResponse = $this->paystackService->initializeTransaction(
            email: $lead->email,
            amountInPesewas: $amount,
            reference: $reference,
            callbackUrl: route('onboarding.payment.callback'),
            metadata: [
                'lead_id' => $lead->id,
                'plan_tier' => $lead->plan_tier,
                'billing_cycle' => $lead->billing_cycle,
                'organization_name' => $lead->organization_name,
            ]
        );

        if (!$paystackResponse || !isset($paystackResponse['authorization_url'])) {
            // Paystack initialization failed - fall back to manual process
            Log::error('Paystack initialization failed for lead', ['lead_id' => $lead->id]);
            
            // Send notifications for manual follow-up
            $this->sendNotifications($lead);
            
            return redirect()->route('onboarding.success')
                ->with('lead', $lead)
                ->with('payment_failed', true);
        }

        // Redirect to Paystack checkout
        return redirect()->away($paystackResponse['authorization_url']);
    }

    public function paymentCallback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->route('onboarding.payment.cancelled')
                ->with('error', 'No payment reference provided.');
        }

        // Find the payment
        $payment = Payment::where('reference', $reference)->first();

        if (!$payment) {
            return redirect()->route('onboarding.payment.cancelled')
                ->with('error', 'Payment not found.');
        }

        // Verify with Paystack
        $verification = $this->paystackService->verifyTransaction($reference);

        if (!$verification || $verification['status'] !== 'success') {
            $payment->markAsFailed();
            return redirect()->route('onboarding.payment.cancelled')
                ->with('error', 'Payment verification failed. Please try again.');
        }

        // Mark payment as successful
        $payment->markAsSuccessful(
            paystackReference: $verification['reference'] ?? $reference,
            channel: $verification['channel'] ?? null,
            metadata: $verification['metadata'] ?? null
        );

        // Update lead status
        $lead = $payment->lead;
        $lead->update(['status' => 'paid']);

        // Redirect to setup choice page
        return redirect()->route('onboarding.setup.choice', ['payment' => $payment->id]);
    }

    public function paymentCancelled()
    {
        return view('onboarding.payment-cancelled');
    }

    public function success()
    {
        return view('onboarding.success');
    }

    /**
     * Show setup choice page (self-setup or skip).
     */
    public function setupChoice(Payment $payment)
    {
        // Ensure payment is successful
        if (!$payment->isSuccessful()) {
            return redirect()->route('onboarding.payment.cancelled')
                ->with('error', 'Payment not verified.');
        }

        return view('onboarding.setup-choice', [
            'payment' => $payment,
            'lead' => $payment->lead,
        ]);
    }

    /**
     * Show organization setup form.
     */
    public function setupOrganization(Payment $payment)
    {
        if (!$payment->isSuccessful()) {
            return redirect()->route('onboarding.payment.cancelled');
        }

        $lead = $payment->lead;
        $timezones = \DateTimeZone::listIdentifiers();

        return view('onboarding.setup-organization', [
            'payment' => $payment,
            'lead' => $lead,
            'timezones' => array_combine($timezones, $timezones),
            'baseDomain' => parse_url(config('app.url'), PHP_URL_HOST) ?? 'elections-hq.com',
        ]);
    }

    /**
     * Store the organization and create admin user.
     */
    public function storeOrganization(Request $request, Payment $payment)
    {
        if (!$payment->isSuccessful()) {
            return redirect()->route('onboarding.payment.cancelled');
        }

        $lead = $payment->lead;

        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'subdomain' => ['required', 'string', 'max:63', 'alpha_dash', 'unique:organizations,subdomain'],
            'timezone' => 'required|string|timezone',
            'logo' => 'nullable|image|max:2048',
            'admin_name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'will_vote' => 'nullable|boolean',
            'voter_id' => 'required_if:will_vote,1|nullable|string|max:50',
        ]);

        // Check uniqueness of voter_id if provided
        if (!empty($validated['voter_id'])) {
            // We can't use standard unique rule easily because organization doesn't exist yet,
            // but since it's a new organization, there are no other users in it yet.
            // So practically, this first user's ID is always unique for this organization.
        }

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('organizations/logos', 'public');
        }

        try {
            // Create Organization using service
            $organization = $this->organizationSetupService->createOrganization(
                lead: $lead,
                data: $validated,
                logoPath: $logoPath
            );

            // Update lead status
            $lead->update(['status' => 'setup_complete']);

            // Send notifications
            $this->sendNotifications($lead, $payment);

            $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'elections-hq.com';
            $loginUrl = "https://{$organization->subdomain}.{$baseDomain}/admin/login";

            return redirect()->route('onboarding.success')
                ->with('organization', $organization)
                ->with('login_url', $loginUrl)
                ->with('setup_complete', true);

        } catch (\Exception $e) {
            Log::error('Organization setup failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Setup failed. Please try again or contact support.');
        }
    }

    /**
     * Skip self-setup - team will call.
     */
    public function skipSetup(Payment $payment)
    {
        if (!$payment->isSuccessful()) {
            return redirect()->route('onboarding.payment.cancelled');
        }

        $lead = $payment->lead;
        
        // Send notifications for manual setup
        $this->sendNotifications($lead, $payment);

        return redirect()->route('onboarding.success')
            ->with('lead', $lead)
            ->with('payment', $payment)
            ->with('skipped_setup', true);
    }

    /**
     * Get the plan amount in pesewas.
     */
    protected function getPlanAmount(string $planTier, string $billingCycle): int
    {
        $plans = config('pricing.plans');

        if (!isset($plans[$planTier])) {
            abort(400, "Invalid plan tier: {$planTier}");
        }

        $amount = $plans[$planTier][$billingCycle] ?? null;

        if (!$amount) {
            abort(400, "Invalid billing cycle for plan: {$planTier}");
        }

        return $amount;
    }

    /**
     * Send email and SMS notifications.
     */
    protected function sendNotifications(Lead $lead, ?Payment $payment = null): void
    {
        try {
            // Send Email to Admin
            Mail::to('joseph.mensah@jbmensah.com')->send(new NewLeadAdminNotification($lead));

            // Send Confirmation to User
            Mail::to($lead->email)->send(new LeadReceivedConfirmation($lead));
        } catch (\Exception $e) {
            Log::error('Failed to send onboarding emails', ['error' => $e->getMessage()]);
        }

        // Send SMS to Admin
        try {
            /** @var \App\Services\SmsService $smsService */
            $smsService = app(\App\Services\SmsService::class);
            
            $paymentNote = $payment ? " Payment: {$payment->formatted_amount}" : "";
            $smsMessage = "New Lead: {$lead->organization_name} (" . ucfirst($lead->plan_tier) . " - " . ucfirst($lead->billing_cycle) . ").{$paymentNote} Contact: {$lead->name}, {$lead->phone}.";
            
            $smsService->send('0246955436', $smsMessage);
        } catch (\Exception $e) {
            Log::error('Failed to send Admin SMS for new lead', ['error' => $e->getMessage()]);
        }
    }
}
