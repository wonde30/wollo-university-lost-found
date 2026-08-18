<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\StorageLocation;
use Illuminate\Database\Seeder;

class StorageLocationSeeder extends Seeder
{
    public function run(): void
    {
        $mainCampus = Campus::where('code', 'MAIN-DESSIE')->first();

        if ($mainCampus) {
            StorageLocation::firstOrCreate(['campus_id' => $mainCampus->id, 'name' => 'Security Office Safe Box A'], [
                'building' => 'Admin Building',
                'room_number' => 'G-12',
                'shelf_cabinet_code' => 'CAB-A1',
                'capacity' => 50,
                'status' => 'active',
            ]);

            StorageLocation::firstOrCreate(['campus_id' => $mainCampus->id, 'name' => 'Lost & Found Storage Room B'], [
                'building' => 'Student Affairs',
                'room_number' => '104',
                'shelf_cabinet_code' => 'SHF-B3',
                'capacity' => 200,
                'status' => 'active',
            ]);
        }
    }
}
