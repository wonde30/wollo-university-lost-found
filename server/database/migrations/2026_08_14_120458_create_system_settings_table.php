<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->text('value');
            $table->enum('type', ['string', 'integer', 'boolean', 'json'])
                  ->default('string');
            $table->string('description', 255)->nullable();
            $table->boolean('is_public')->default(false); // visible to frontend without auth
            $table->timestamps();
        });

        // Real WU operational defaults
        DB::table('system_settings')->insert([
            ['key' => 'item_expiry_days',          'value' => '90',    'type' => 'integer', 'description' => 'Days before inactive lost/found item auto-expires',              'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'match_score_threshold',     'value' => '35.00', 'type' => 'string',  'description' => 'Minimum Jaccard score to create a match suggestion',            'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_photos_per_item',       'value' => '3',     'type' => 'integer', 'description' => 'Maximum photos allowed per item report',                        'is_public' => true,  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_photo_size_mb',         'value' => '5',     'type' => 'integer', 'description' => 'Maximum file size per photo in megabytes',                      'is_public' => true,  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'otp_expiry_minutes',        'value' => '10',    'type' => 'integer', 'description' => 'Minutes before email OTP expires',                              'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'login_lockout_attempts',    'value' => '5',     'type' => 'integer', 'description' => 'Failed login attempts before 30-minute lockout',               'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'login_lockout_minutes',     'value' => '30',    'type' => 'integer', 'description' => 'Lockout duration in minutes after max failed attempts',         'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'expiry_warning_days',       'value' => '7',     'type' => 'integer', 'description' => 'Days before expiry to send warning email to reporter',          'is_public' => false, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'items_per_page_default',    'value' => '20',    'type' => 'integer', 'description' => 'Default pagination page size on item listings',                 'is_public' => true,  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'maintenance_mode',          'value' => 'false', 'type' => 'boolean', 'description' => 'Put site in maintenance mode (shows banner to all users)',      'is_public' => true,  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'allow_public_browse',       'value' => 'true',  'type' => 'boolean', 'description' => 'Allow unauthenticated visitors to browse found items',          'is_public' => true,  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'university_name',           'value' => 'Wollo University', 'type' => 'string', 'description' => 'University name shown in emails and PDFs',           'is_public' => true,  'created_at' => now(), 'updated_at' => now()],
            ['key' => 'support_email',             'value' => 'ict@wu.edu.et', 'type' => 'string', 'description' => 'ICT support email shown on error pages and emails',    'is_public' => true,  'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
