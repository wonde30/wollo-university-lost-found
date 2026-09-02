<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'Wollo University - Lost & Found System',
                'type' => 'string',
                'display_name' => 'Platform Name',
                'description' => 'Display title of the recovery portal across all campus interfaces.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'contact_email',
                'value' => 'support@wu.edu.et',
                'type' => 'string',
                'display_name' => 'Central Support Email',
                'description' => 'Inquiry address for students and staff facing recovery or custody escalations.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+251-33-311-5200',
                'type' => 'string',
                'display_name' => 'Campus Security Hotline',
                'description' => 'Emergency helpline for immediate campus custody handovers.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'default_campus_id',
                'value' => '1',
                'type' => 'integer',
                'display_name' => 'Default Campus ID',
                'description' => 'Default campus selected when creating new reports or custody events.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'item_expiry_days',
                'value' => '90',
                'type' => 'integer',
                'display_name' => 'Item Expiry Window (Days)',
                'description' => 'Number of days before unclaimed found items or inactive lost reports expire.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'expiry_warning_days',
                'value' => '7',
                'type' => 'integer',
                'display_name' => 'Expiry Warning Lead Time (Days)',
                'description' => 'Days prior to item expiration when automated alert notices are sent.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'item_edit_window_lost_hours',
                'value' => '48',
                'type' => 'integer',
                'display_name' => 'Lost Item Edit Window (Hours)',
                'description' => 'Allowed timeframe for students to edit their lost item report details.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'item_edit_window_found_hours',
                'value' => '24',
                'type' => 'integer',
                'display_name' => 'Found Item Edit Window (Hours)',
                'description' => 'Allowed timeframe for finders to edit report details prior to secure custody locking.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'match_score_threshold',
                'value' => '35.00',
                'type' => 'float',
                'display_name' => 'AI Match Score Threshold (%)',
                'description' => 'Minimum similarity threshold for automated lost-found match suggestions.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'otp_expiry_minutes',
                'value' => '10',
                'type' => 'integer',
                'display_name' => 'OTP Expiration Duration (Minutes)',
                'description' => 'Validity window for password reset and email verification one-time passwords.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'login_lockout_attempts',
                'value' => '5',
                'type' => 'integer',
                'display_name' => 'Max Failed Login Attempts',
                'description' => 'Number of consecutive authentication failures triggering account cooldown.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'login_lockout_minutes',
                'value' => '30',
                'type' => 'integer',
                'display_name' => 'Account Lockout Duration (Minutes)',
                'description' => 'Duration of temporary account lock following brute-force threshold exceedance.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'enable_email_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'display_name' => 'Enable Email Delivery',
                'description' => 'Dispatch outgoing transactional SMTP emails for claims, matches, and returns.',
                'is_public' => false,
                'is_editable' => true,
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        \Illuminate\Support\Facades\Cache::forget('settings.all');
    }
}

