<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $mainCampus = Campus::where('code', 'MAIN-DESSIE')->first();
        $kiotCampus = Campus::where('code', 'KIOT-KOMB')->first();

        if ($mainCampus) {
            Location::firstOrCreate(['campus_id' => $mainCampus->id, 'name' => 'Central Library Ground Floor'], [
                'building' => 'Central Library',
                'floor' => 'Ground Floor',
                'is_active' => true,
            ]);

            Location::firstOrCreate(['campus_id' => $mainCampus->id, 'name' => 'Student Cafeteria Hall 1'], [
                'building' => 'Student Union',
                'floor' => '1st Floor',
                'is_active' => true,
            ]);
        }

        if ($kiotCampus) {
            Location::firstOrCreate(['campus_id' => $kiotCampus->id, 'name' => 'ICT Lab 3'], [
                'building' => 'Computing Building',
                'floor' => '2nd Floor',
                'room_number' => 'C-203',
                'is_active' => true,
            ]);
        }
    }
}
