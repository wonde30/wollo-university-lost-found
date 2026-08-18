<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained('campuses')->restrictOnDelete();
            $table->string('name', 150);
            $table->string('short_code', 20);
            $table->enum('type', ['college', 'school', 'institute', 'department', 'office'])
                  ->default('college');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['campus_id', 'short_code']);
        });

        // Dessie campus (campus_id = 1)
        $dessie = [
            ['campus_id' => 1, 'name' => 'College of Agriculture',                             'short_code' => 'AGR',  'type' => 'college'],
            ['campus_id' => 1, 'name' => 'College of Business and Economics',                  'short_code' => 'BEC',  'type' => 'college'],
            ['campus_id' => 1, 'name' => 'College of Medicine and Health Sciences',            'short_code' => 'MED',  'type' => 'college'],
            ['campus_id' => 1, 'name' => 'College of Natural and Computational Sciences',      'short_code' => 'NCS',  'type' => 'college'],
            ['campus_id' => 1, 'name' => 'College of Social Sciences and Humanities',          'short_code' => 'SSH',  'type' => 'college'],
            ['campus_id' => 1, 'name' => 'College of Law and Governance',                      'short_code' => 'LAW',  'type' => 'college'],
            ['campus_id' => 1, 'name' => 'School of Veterinary Medicine',                      'short_code' => 'VET',  'type' => 'school'],
            ['campus_id' => 1, 'name' => 'Institute of Teacher Education',                     'short_code' => 'ITE',  'type' => 'institute'],
            ['campus_id' => 1, 'name' => 'University Library',                                 'short_code' => 'LIB',  'type' => 'office'],
            ['campus_id' => 1, 'name' => 'Security Office — Dessie',                          'short_code' => 'SECD', 'type' => 'office'],
            ['campus_id' => 1, 'name' => 'ICT Office — Dessie',                               'short_code' => 'ICTD', 'type' => 'office'],
        ];

        // KIoT campus (campus_id = 2)
        $kiot = [
            ['campus_id' => 2, 'name' => 'College of Engineering and Technology',              'short_code' => 'ENG',  'type' => 'college'],
            ['campus_id' => 2, 'name' => 'College of Informatics',                             'short_code' => 'INF',  'type' => 'college'],
            ['campus_id' => 2, 'name' => 'School of Textile and Fashion Design',               'short_code' => 'TFD',  'type' => 'school'],
            ['campus_id' => 2, 'name' => 'Security Office — KIoT',                            'short_code' => 'SECK', 'type' => 'office'],
            ['campus_id' => 2, 'name' => 'ICT Office — KIoT',                                 'short_code' => 'ICTK', 'type' => 'office'],
        ];

        $now = now();
        foreach (array_merge($dessie, $kiot) as $row) {
            $row['is_active'] = true;
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            DB::table('departments')->insert($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
