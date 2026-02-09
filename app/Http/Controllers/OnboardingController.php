<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Mail\NewLeadAdminNotification;
use App\Mail\LeadReceivedConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
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
            'plan_tier' => ['required', Rule::in(['new', 'basic', 'premium', 'enterprise'])],
            'billing_cycle' => ['required', Rule::in(['monthly', 'annual'])],
            'message' => 'nullable|string|max:1000',
        ]);

        $lead = Lead::create([
            ...$validated,
            'ip_address' => $request->ip(),
            'status' => 'new',
        ]);

        // Send Email to Admin
        Mail::to('joseph.mensah@jbmensah.com')->send(new NewLeadAdminNotification($lead));

        // Send Confirmation to User
        Mail::to($lead->email)->send(new LeadReceivedConfirmation($lead));

        // Send SMS to Admin
        try {
            /** @var \App\Services\SmsService $smsService */
            $smsService = app(\App\Services\SmsService::class);
            
            $smsMessage = "New Lead: {$lead->organization_name} (" . ucfirst($lead->plan_tier) . " - " . ucfirst($lead->billing_cycle) . "). Contact: {$lead->name}, {$lead->phone}.";
            
            $smsService->send('0246955436', $smsMessage);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send Admin SMS for new lead', ['error' => $e->getMessage()]);
        }

        return redirect()->route('onboarding.success')->with('lead', $lead);
    }

    public function success()
    {
        return view('onboarding.success');
    }
}
