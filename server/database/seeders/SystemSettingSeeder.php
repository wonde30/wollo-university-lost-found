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
            // =====================================================
            // BRANDING — Institution Identity
            // =====================================================
            [
                'key' => 'institution_name',
                'value' => 'Wollo University',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'Institution Name',
                'description' => 'Full official name of the institution displayed across all interfaces, emails, and reports.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'institution_short_name',
                'value' => 'WU',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'Institution Short Name / Abbreviation',
                'description' => 'Abbreviation used in reference codes, CSV export filenames, and compact UI elements.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'institution_website',
                'value' => 'https://wu.edu.et',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'Institution Website URL',
                'description' => 'Official institution website linked in footer and public pages.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'site_name',
                'value' => 'Wollo University - Lost & Found System',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'Platform Name',
                'description' => 'Full display title of the platform shown in browser tab, emails, and PDF reports.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'system_short_name',
                'value' => 'WU-LFMS',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'System Short Name',
                'description' => 'Short system identifier used in PDF headers, reference codes, and official documents.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'institution_tagline',
                'value' => 'Property Recovery Portal',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'Institution Tagline / Subtitle',
                'description' => 'Short tagline displayed under the logo across headers and auth pages.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'institution_description',
                'value' => 'The verified property registry for students, instructors, and staff across all campuses.',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'Institution Description',
                'description' => 'Descriptive paragraph used on the homepage hero section and meta descriptions.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'institution_campuses_text',
                'value' => 'Dessie Main Campus & Kombolcha Institute of Technology (KIOT)',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'Campuses Description Text',
                'description' => 'List of campus names shown in footers, emails, and PDF reports.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'logo_url',
                'value' => '/images/wu-logo.png',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'Logo Image URL',
                'description' => 'Path or URL to the institution logo displayed across all pages. Upload a new logo to replace.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'developer_credit',
                'value' => 'Powered by WONDATIR (IT)',
                'type' => 'string',
                'group' => 'branding',
                'display_name' => 'Developer Credit Text',
                'description' => 'Attribution text shown in the dashboard footer.',
                'is_public' => true,
                'is_editable' => true,
            ],

            // =====================================================
            // APPEARANCE — Theme & Colors
            // =====================================================
            [
                'key' => 'theme_primary_color',
                'value' => '#0B5D3B',
                'type' => 'string',
                'group' => 'appearance',
                'display_name' => 'Primary Brand Color',
                'description' => 'Main brand color used for headers, buttons, links, and accents (hex format, e.g. #0B5D3B).',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'theme_primary_light',
                'value' => '#75bd97',
                'type' => 'string',
                'group' => 'appearance',
                'display_name' => 'Primary Color (Light Variant)',
                'description' => 'Lighter shade of the primary color used in dark mode text and subtle accents.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'theme_primary_hover',
                'value' => '#084C30',
                'type' => 'string',
                'group' => 'appearance',
                'display_name' => 'Primary Color (Hover State)',
                'description' => 'Darker shade of the primary color used for button hover and active states.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'theme_accent_color',
                'value' => '#D4AF37',
                'type' => 'string',
                'group' => 'appearance',
                'display_name' => 'Accent / Gold Color',
                'description' => 'Secondary accent color used for badges, highlights, and decorative elements.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'theme_primary_bg_light',
                'value' => '#E8F4EE',
                'type' => 'string',
                'group' => 'appearance',
                'display_name' => 'Primary Background (Light Mode)',
                'description' => 'Light tinted background color for selected/active states in light mode.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'theme_primary_bg_dark',
                'value' => '#153C2D',
                'type' => 'string',
                'group' => 'appearance',
                'display_name' => 'Primary Background (Dark Mode)',
                'description' => 'Tinted background color for selected/active states in dark mode.',
                'is_public' => true,
                'is_editable' => true,
            ],

            // =====================================================
            // LOCALE — Language & Internationalization
            // =====================================================
            [
                'key' => 'default_locale',
                'value' => 'en',
                'type' => 'string',
                'group' => 'locale',
                'display_name' => 'Default Language',
                'description' => 'Default language code for new users and public pages (e.g. en, am).',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'available_locales',
                'value' => 'en,am',
                'type' => 'string',
                'group' => 'locale',
                'display_name' => 'Available Languages',
                'description' => 'Comma-separated list of available language codes (e.g. en,am,om).',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'institution_name_am',
                'value' => 'ወሎ ዩኒቨርሲቲ',
                'type' => 'string',
                'group' => 'locale',
                'display_name' => 'Institution Name (Amharic)',
                'description' => 'Institution name in Amharic script, displayed when language is set to Amharic.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'institution_tagline_am',
                'value' => 'የንብረት መልሶ ማግኛ ፖርታል',
                'type' => 'string',
                'group' => 'locale',
                'display_name' => 'Institution Tagline (Amharic)',
                'description' => 'Tagline in Amharic displayed under the logo when language is Amharic.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'app_name_am',
                'value' => 'የወሎ ዩኒቨርሲቲ የጠፉና የተገኙ ዕቃዎች',
                'type' => 'string',
                'group' => 'locale',
                'display_name' => 'App Name (Amharic)',
                'description' => 'Full application name in Amharic.',
                'is_public' => true,
                'is_editable' => true,
            ],

            // =====================================================
            // CONTACT — Support & Communication
            // =====================================================
            [
                'key' => 'contact_email',
                'value' => 'support@wu.edu.et',
                'type' => 'string',
                'group' => 'contact',
                'display_name' => 'Central Support Email',
                'description' => 'Inquiry address for students and staff facing recovery or custody escalations.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+251-33-311-5200',
                'type' => 'string',
                'group' => 'contact',
                'display_name' => 'Campus Security Hotline',
                'description' => 'Emergency helpline for immediate campus custody handovers.',
                'is_public' => true,
                'is_editable' => true,
            ],
            [
                'key' => 'email_footer_text',
                'value' => 'Campus Property Recovery Office',
                'type' => 'string',
                'group' => 'contact',
                'display_name' => 'Email Footer Office Name',
                'description' => 'Office name displayed in the footer of all outgoing email notifications.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'pdf_footer_text',
                'value' => 'Dessie & Kombolcha Campuses • WU-LFMS Official System • Confidential',
                'type' => 'string',
                'group' => 'contact',
                'display_name' => 'PDF Report Footer Text',
                'description' => 'Confidentiality and branding text shown at the bottom of all generated PDF reports.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'csv_export_prefix',
                'value' => 'wu',
                'type' => 'string',
                'group' => 'contact',
                'display_name' => 'CSV Export Filename Prefix',
                'description' => 'Short prefix for exported CSV filenames (e.g. "wu" produces "wu_items_2026-09-09.csv").',
                'is_public' => true,
                'is_editable' => true,
            ],

            // =====================================================
            // BEHAVIOR — Item & Claim Operational Settings
            // =====================================================
            [
                'key' => 'default_campus_id',
                'value' => '1',
                'type' => 'integer',
                'group' => 'behavior',
                'display_name' => 'Default Campus ID',
                'description' => 'Default campus selected when creating new reports or custody events.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'item_expiry_days',
                'value' => '90',
                'type' => 'integer',
                'group' => 'behavior',
                'display_name' => 'Item Expiry Window (Days)',
                'description' => 'Number of days before unclaimed found items or inactive lost reports expire.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'expiry_warning_days',
                'value' => '7',
                'type' => 'integer',
                'group' => 'behavior',
                'display_name' => 'Expiry Warning Lead Time (Days)',
                'description' => 'Days prior to item expiration when automated alert notices are sent.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'item_edit_window_lost_hours',
                'value' => '48',
                'type' => 'integer',
                'group' => 'behavior',
                'display_name' => 'Lost Item Edit Window (Hours)',
                'description' => 'Allowed timeframe for students to edit their lost item report details.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'item_edit_window_found_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'behavior',
                'display_name' => 'Found Item Edit Window (Hours)',
                'description' => 'Allowed timeframe for finders to edit report details prior to secure custody locking.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'match_score_threshold',
                'value' => '35.00',
                'type' => 'float',
                'group' => 'behavior',
                'display_name' => 'AI Match Score Threshold (%)',
                'description' => 'Minimum similarity threshold for automated lost-found match suggestions.',
                'is_public' => false,
                'is_editable' => true,
            ],

            // =====================================================
            // SECURITY — Authentication & Lockout
            // =====================================================
            [
                'key' => 'otp_expiry_minutes',
                'value' => '10',
                'type' => 'integer',
                'group' => 'security',
                'display_name' => 'OTP Expiration Duration (Minutes)',
                'description' => 'Validity window for password reset and email verification one-time passwords.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'login_lockout_attempts',
                'value' => '5',
                'type' => 'integer',
                'group' => 'security',
                'display_name' => 'Max Failed Login Attempts',
                'description' => 'Number of consecutive authentication failures triggering account cooldown.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'login_lockout_minutes',
                'value' => '30',
                'type' => 'integer',
                'group' => 'security',
                'display_name' => 'Account Lockout Duration (Minutes)',
                'description' => 'Duration of temporary account lock following brute-force threshold exceedance.',
                'is_public' => false,
                'is_editable' => true,
            ],
            [
                'key' => 'enable_email_notifications',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'security',
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
        \Illuminate\Support\Facades\Cache::forget('settings.public');
    }
}
