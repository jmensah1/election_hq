@extends('layouts.email')

@section('title', 'Vote Confirmation')

@section('header', 'Vote Confirmation')

@section('content')
<p style="color: #334155; font-size: 16px; line-height: 1.5;">
    Hello {{ $name }},
</p>

<p style="color: #334155; font-size: 16px; line-height: 1.5;">
    This email confirms that your vote has been successfully cast in the election: <strong>{{ $electionTitle }}</strong>.
</p>

<p style="color: #334155; font-size: 16px; line-height: 1.5; background-color: #eff6ff; padding: 16px; border-radius: 6px; border-left: 4px solid #3b82f6;">
    <strong>Time of Vote:</strong> {{ $time }}
</p>

<p style="color: #334155; font-size: 16px; line-height: 1.5;">
    For security and anonymity reasons, this confirmation does not list your specific choices. However, your participation has been recorded in our secure, anonymous ledger.
</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="{{ route('voter.dashboard') }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; font-weight: bold; padding: 12px 24px; border-radius: 6px; text-decoration: none;">
        Go to Voter Dashboard
    </a>
</div>

<p style="color: #64748b; font-size: 14px; margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
    If you have any questions or believe this is an error, please contact the election commission.
</p>
@endsection
