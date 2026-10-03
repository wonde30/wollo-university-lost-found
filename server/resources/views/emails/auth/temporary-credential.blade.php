@extends('emails.layouts.base')

@section('content')
    <h2 style="margin-top: 0; color: #0F5132; font-size: 18px; font-weight: 800;">Your Account Credentials</h2>
    <p>Dear {{ $user->full_name ?? 'Student / Staff Member' }},</p>
    <p>Your institutional email address has been successfully verified with the <strong>{{ \App\Models\SystemSetting::get('site_name', 'Wollo University Lost & Found Portal') }}</strong>.</p>
    <p>A secure temporary password has been automatically generated for your initial login:</p>

    <div class="otp-code" style="font-family: monospace; letter-spacing: 2px; background: #f8fafc; border: 1px dashed #0F5132; padding: 12px; font-size: 20px; font-weight: bold; text-align: center; color: #0F5132; margin: 20px 0;">
        {{ $password }}
    </div>

    <div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 12px; margin-bottom: 20px;">
        <p style="margin: 0; font-size: 13px; color: #991b1b; font-weight: bold;">
            SECURITY REQUIREMENT:
        </p>
        <p style="margin: 4px 0 0; font-size: 12px; color: #b91c1c;">
            This is a temporary generated password. You must change your password immediately upon your first login before accessing portal services.
        </p>
    </div>

    <p style="font-size: 13px; color: #64748b;">
        If you did not register for this account, please notify the ICT Security Office immediately.
    </p>
@endsection
