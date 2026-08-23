@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #0F5132; font-size: 18px; font-weight: 800;">Verify Your Email Address</h2>
    <p>Dear {{ $user->full_name ?? 'Student / Staff Member' }},</p>
    <p>Thank you for registering with the <strong>Wollo University Lost & Found System</strong>. Use the verification code below to confirm your university email address and activate your account:</p>

    <div class="otp-code">
        {{ $code }}
    </div>

    <p style="font-size: 13px; color: #64748b;">
        This single-use code is valid for <strong>15 minutes</strong>. If you did not create an account on the Wollo University Lost & Found platform, you can safely ignore this message.
    </p>
@endsection
