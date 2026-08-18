<?php

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        Campus::firstOrCreate(['code' => 'MAIN-DESSIE'], [
            'name' => 'Dessie Main Campus',
            'address' => 'Dessie, Amhara Region, Ethiopia',
            'description' => 'Wollo University Main Academic Campus',
            'is_active' => true,
        ]);

        Campus::firstOrCreate(['code' => 'KIOT-KOMB'], [
            'name' => 'Kombolcha Institute of Technology (KIOT)',
            'address' => 'Kombolcha, Amhara Region, Ethiopia',
            'description' => 'Engineering and Technology Campus',
            'is_active' => true,
        ]);

        Campus::firstOrCreate(['code' => 'CMHS-DESSIE'], [
            'name' => 'College of Medicine & Health Sciences Campus',
            'address' => 'Dessie, Amhara Region, Ethiopia',
            'description' => 'Medical and Health Sciences Campus',
            'is_active' => true,
        ]);
    }
}
