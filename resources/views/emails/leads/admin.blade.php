@component('mail::message')
# New Application Received

A new organization has applied to use Elections HQ.

**Plan:** {{ ucfirst($lead->plan_tier) }} ({{ ucfirst($lead->billing_cycle) }})

**Contact Details:**
*   **Name:** {{ $lead->name }}
*   **Organization:** {{ $lead->organization_name }}
*   **Email:** {{ $lead->email }}
*   **Phone:** {{ $lead->phone }}

@if($lead->message)
**Message:**
{{ $lead->message }}
@endif

@component('mail::button', ['url' => config('app.url') . '/admin/leads'])
View in Admin Panel
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
