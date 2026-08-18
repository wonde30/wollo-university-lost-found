<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')
                  ->constrained('campuses')
                  ->restrictOnDelete();
            $table->string('name', 100);
            $table->string('code', 30)->unique();
            $table->string('description', 255)->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('storage_locations')->insert([
            ['campus_id'=>1,'name'=>'Dessie Security Office — Main Cabinet', 'code'=>'DSS-SEC-CAB1', 'description'=>'Main locked cabinet at Dessie Security Office gate', 'capacity'=>50, 'is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['campus_id'=>1,'name'=>'Dessie Security Office — Shelf A',      'code'=>'DSS-SEC-SHF-A','description'=>'Open shelf for large items (bags, equipment)',       'capacity'=>20, 'is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['campus_id'=>1,'name'=>'Dessie Security Office — Safe',         'code'=>'DSS-SEC-SAFE', 'description'=>'Safe for high-value items (phones, wallets, IDs)',  'capacity'=>10, 'is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['campus_id'=>2,'name'=>'KIoT Security Office — Main Cabinet',   'code'=>'KIT-SEC-CAB1', 'description'=>'Main locked cabinet at KIoT Security Office gate',  'capacity'=>30, 'is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['campus_id'=>2,'name'=>'KIoT Security Office — Shelf A',        'code'=>'KIT-SEC-SHF-A','description'=>'Open shelf for large items',                       'capacity'=>15, 'is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('storage_locations');
    }
};
