<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('short_code', 10)->unique();
            $table->string('city', 80);
            $table->string('region', 80)->default('Amhara');
            $table->string('address', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 191)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('campuses')->insert([
            [
                'name'       => 'Dessie Main Campus',
                'short_code' => 'DSS',
                'city'       => 'Dessie',
                'region'     => 'Amhara',
                'address'    => 'Dessie, South Wollo Zone, Amhara Region, Ethiopia',
                'phone'      => '+251-33-111-xxxx',
                'email'      => 'registrar@wu.edu.et',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'Kombolcha Institute of Technology (KIoT)',
                'short_code' => 'KIT',
                'city'       => 'Kombolcha',
                'region'     => 'Amhara',
                'address'    => 'Kombolcha, South Wollo Zone, Amhara Region, Ethiopia',
                'phone'      => '+251-33-551-xxxx',
                'email'      => 'kiot@wu.edu.et',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('campuses');
    }
};
