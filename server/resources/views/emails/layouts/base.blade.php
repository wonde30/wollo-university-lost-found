<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? \App\Models\SystemSetting::get('site_name', 'Lost & Found Notification') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 24px;
            line-height: 1.6;
        }
        .email-container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .email-header {
            background: linear-gradient(135deg, {{ \App\Models\SystemSetting::get('theme_primary_color', '#0F5132') }} 0%, {{ \App\Models\SystemSetting::get('theme_primary_hover', '#0B3822') }} 100%);
            padding: 28px 32px;
            text-align: center;
            color: #ffffff;
        }
        .email-header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #ffffff;
        }
        .email-header p {
            margin: 4px 0 0;
            font-size: 12px;
            color: {{ \App\Models\SystemSetting::get('theme_accent_color', '#D4AF37') }};
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .email-body {
            padding: 32px;
        }
        .email-footer {
            background-color: #f1f5f9;
            padding: 20px 32px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-success { background-color: #dcfce7; color: #15803d; }
        .badge-warning { background-color: #fef3c7; color: #b45309; }
        .badge-info { background-color: #e0f2fe; color: #0369a1; }
        .badge-gold { background-color: #fef9c3; color: #854d0e; }
        .btn {
            display: inline-block;
            background-color: {{ \App\Models\SystemSetting::get('theme_primary_color', '#0F5132') }};
            color: #ffffff !important;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            margin-top: 16px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            background-color: #f8fafc;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .info-table td {
            padding: 10px 14px;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table td:first-child {
            font-weight: 600;
            color: #64748b;
            width: 35%;
        }
        .info-table tr:last-child td {
            border-bottom: none;
        }
        .otp-code {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 6px;
            color: {{ \App\Models\SystemSetting::get('theme_primary_color', '#0F5132') }};
            background-color: #f0fdf4;
            border: 2px dashed #86efac;
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            margin: 20px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{{ \App\Models\SystemSetting::get('institution_name', 'University') }}</h1>
            <p>Lost & Found {{ \App\Models\SystemSetting::get('institution_tagline', 'Property System') }}</p>
        </div>
        <div class="email-body">
            @yield('content')
        </div>
        <div class="email-footer">
            <p style="margin: 0 0 6px;">{{ \App\Models\SystemSetting::get('institution_name', 'University') }} {{ \App\Models\SystemSetting::get('email_footer_text', 'Campus Property Recovery Office') }}</p>
            <p style="margin: 0;">{{ \App\Models\SystemSetting::get('institution_campuses_text', '') }}</p>
            <p style="margin: 6px 0 0; font-size: 11px; color: #94a3b8;">This is an automated system notification. Please do not reply directly to this email.</p>
        </div>
    </div>
</body>
</html>
