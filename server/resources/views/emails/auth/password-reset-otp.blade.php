@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #0F5132; font-size: 18px; font-weight: 800;">Password Reset Request</h2>
    <p>Dear {{ $user->full_name ?? 'User' }},</p>
    <p>We received a request to reset your password for your <strong>{{ \App\Models\SystemSetting::get('site_name', 'Lost & Found') }}</strong> account. Use the one-time code below to proceed with resetting your password:</p>

    <div class="otp-code">
        {{ $code }}
    </div>

    <p style="font-size: 13px; color: #64748b;">
        This code is valid for <strong>15 minutes</strong>. If you did not request a password reset, please change your university credentials immediately or contact ICT security.
    </p>
@endsection
