<x-mail::message>
# New Contact Form Submission

You have received a new message from the Elections HQ contact form.

**Name:** {{ $data['name'] }}<br>
**Email:** {{ $data['email'] }}<br>
**Phone:** {{ $data['phone'] ?? 'N/A' }}

**Message:**
{{ $data['message'] }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
