<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\StorageLocation;
use Illuminate\Database\Seeder;

class StorageLocationSeeder extends Seeder
{
    public function run(): void
    {
        $mainCampus = Campus::where('short_code', 'DSS')->first() ?? Campus::first();
        $kiotCampus = Campus::where('short_code', 'KIT')->first() ?? $mainCampus;
        $titaCampus = Campus::where('short_code', 'TITA')->first() ?? $mainCampus;

        $storageLocations = [
            // Dessie Main Campus
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-SEC-CAB1',
                'name' => 'Dessie Security Vault — Cabinet A (Electronics)',
                'description' => 'Secure locked metal cabinet dedicated to laptops, chargers, headphones, and flash drives.',
                'capacity' => 60,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-SEC-CAB2',
                'name' => 'Dessie Security Vault — Cabinet B (IDs & Documents)',
                'description' => 'Document filing cabinet for student IDs, national IDs, bank cards, wallets, and textbooks.',
                'capacity' => 150,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-SEC-SAFE',
                'name' => 'Dessie Security Vault — Heavy Fireproof Safe',
                'description' => 'Double-locked safe for high-value smartphones, tablets, smartwatches, jewelry, and cash.',
                'capacity' => 25,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-LIB-DESK',
                'name' => 'Main Library Front Desk — Intake Holding Bin',
                'description' => 'Temporary daytime holding bin for items turned in by librarians prior to vault transfer.',
                'capacity' => 30,
                'is_active' => true,
            ],

            // KIoT Kombolcha Campus
            [
                'campus_id' => $kiotCampus->id,
                'code' => 'KIT-SEC-BAY1',
                'name' => 'KIoT Central Depot — Locker Bay 1 (Electronics)',
                'description' => 'Secure locker bay at KIoT security depot for computing equipment and gadgets.',
                'capacity' => 45,
                'is_active' => true,
            ],
            [
                'campus_id' => $kiotCampus->id,
                'code' => 'KIT-SEC-BIN2',
                'name' => 'KIoT Central Depot — Bin 2 (Engineering Tools & Gear)',
                'description' => 'Storage bin for workshop gear, drawing kits, calculators, and coats.',
                'capacity' => 80,
                'is_active' => true,
            ],
            [
                'campus_id' => $kiotCampus->id,
                'code' => 'KIT-SEC-SAFE',
                'name' => 'KIoT Security Office — Safe Box',
                'description' => 'Small safe for phones, smartwatches, and wallets.',
                'capacity' => 15,
                'is_active' => true,
            ],

            // Tita Health Sciences Campus
            [
                'campus_id' => $titaCampus->id,
                'code' => 'TITA-SEC-CAB1',
                'name' => 'Tita Security Post — Cabinet 1 (Medical Kits & Books)',
                'description' => 'Storage cabinet for textbooks, lab coats, medical instruments, and bags at Tita.',
                'capacity' => 40,
                'is_active' => true,
            ],
            [
                'campus_id' => $titaCampus->id,
                'code' => 'TITA-SEC-SAFE',
                'name' => 'Tita Security Post — Small Safe',
                'description' => 'Safe for IDs, phones, and high-value personal effects.',
                'capacity' => 15,
                'is_active' => true,
            ],
        ];

        foreach ($storageLocations as $storage) {
            StorageLocation::updateOrCreate(
                ['code' => $storage['code']],
                $storage
            );
        }
    }
}
