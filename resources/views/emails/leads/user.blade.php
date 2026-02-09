@component('mail::message')
# Application Received

Dear {{ $lead->name }},

Thank you for choosing **Elections HQ**. We have received your application for the **{{ ucfirst($lead->plan_tier) }} ({{ ucfirst($lead->billing_cycle) }})** plan.

Our team is currently reviewing your details. We will contact you shortly regarding payment and to complete the setup of your secure election portal.

**Your Details:**
*   **Organization:** {{ $lead->organization_name }}
*   **Plan:** {{ ucfirst($lead->plan_tier) }} ({{ ucfirst($lead->billing_cycle) }})

If you have any urgent questions, please reply to joseph.mensah@jbmensah.com.

Best regards,<br>
The Elections HQ Team
@endcomponent
