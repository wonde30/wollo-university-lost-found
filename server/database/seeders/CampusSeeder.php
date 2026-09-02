<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campus;
use Illuminate\Database\Seeder;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        $campuses = [
            [
                'short_code' => 'DSS',
                'name' => 'Dessie Main Campus',
                'city' => 'Dessie',
                'region' => 'Amhara',
                'address' => 'Dessie Main Campus, P.O. Box 1145, Dessie, Ethiopia',
                'phone' => '+251-33-111-0001',
                'email' => 'registrar@wu.edu.et',
                'is_active' => true,
            ],
            [
                'short_code' => 'KIT',
                'name' => 'Kombolcha Institute of Technology (KIoT)',
                'city' => 'Kombolcha',
                'region' => 'Amhara',
                'address' => 'KIoT Campus, Industrial Zone Road, Kombolcha, Ethiopia',
                'phone' => '+251-33-551-0002',
                'email' => 'kiot@wu.edu.et',
                'is_active' => true,
            ],
            [
                'short_code' => 'TITA',
                'name' => 'Tita Campus (Health & Medical Sciences)',
                'city' => 'Dessie',
                'region' => 'Amhara',
                'address' => 'Tita Health Sciences Campus, Dessie Zuria, Ethiopia',
                'phone' => '+251-33-112-0003',
                'email' => 'cmhs@wu.edu.et',
                'is_active' => true,
            ],
        ];

        foreach ($campuses as $campusData) {
            Campus::updateOrCreate(
                ['short_code' => $campusData['short_code']],
                $campusData
            );
        }
    }
}
