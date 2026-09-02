<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $mainCampus = Campus::where('short_code', 'DSS')->first() ?? Campus::first();
        $kiotCampus = Campus::where('short_code', 'KIT')->first() ?? $mainCampus;
        $titaCampus = Campus::where('short_code', 'TITA')->first() ?? $mainCampus;

        $locations = [
            // Dessie Main Campus
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-LIB-GF',
                'name' => 'Main Library — Ground Floor',
                'name_am' => 'ዋና ቤተ-መጻሕፍት — ታችኛ ፎቅ',
                'building' => 'Main Library',
                'zone' => 'library',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-LIB-1F',
                'name' => 'Main Library — 1st Floor Quiet Study Area',
                'name_am' => 'ዋና ቤተ-መጻሕፍት — 1ኛ ፎቅ ጸጥታ ጥናት ክፍል',
                'building' => 'Main Library',
                'zone' => 'library',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-LIB-2F',
                'name' => 'Main Library — 2nd Floor Digital Lab',
                'name_am' => 'ዋና ቤተ-መጻሕፍት — 2ኛ ፎቅ ዲጂታል ላብ',
                'building' => 'Main Library',
                'zone' => 'library',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-CAF-1',
                'name' => 'Student Cafeteria Hall 1',
                'name_am' => 'የተማሪዎች ካፌ አዳራሽ 1',
                'building' => 'Student Union Complex',
                'zone' => 'cafeteria',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-LEC-402',
                'name' => 'Block 402 Main Lecture Theater',
                'name_am' => 'ብሎክ 402 ዋና የመማሪያ አዳራሽ',
                'building' => 'Block 402',
                'zone' => 'academic',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-LAB-CS',
                'name' => 'Computing Center — CS Lab 1',
                'name_am' => 'የኮምፒውተር ማዕከል — የኮምፒውተር ሳይንስ ላብ 1',
                'building' => 'CNCS Building',
                'zone' => 'academic',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-STAD',
                'name' => 'Main Stadium & Sports Complex',
                'name_am' => 'ዋና ስታዲየም እና የስፖርት ሜዳ',
                'building' => 'Sports Center',
                'zone' => 'sports',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-GATE-1',
                'name' => 'Main Gate 1 Security Checkpoint',
                'name_am' => 'ዋና በር 1 የደህንነት መቆጣጠሪያ',
                'building' => 'Gate 1 Station',
                'zone' => 'security',
                'sort_order' => 8,
                'is_active' => true,
            ],
            [
                'campus_id' => $mainCampus->id,
                'code' => 'DSS-REG',
                'name' => 'Registrar Building Ground Corridor',
                'name_am' => 'ሬጅስትራር ህንፃ መተላለፊያ',
                'building' => 'Registrar Office',
                'zone' => 'administration',
                'sort_order' => 9,
                'is_active' => true,
            ],

            // KIoT Kombolcha Campus
            [
                'campus_id' => $kiotCampus->id,
                'code' => 'KIT-ICT-L3',
                'name' => 'KIoT Computing Building — ICT Lab 3',
                'name_am' => 'ኪዮት ኮምፒውቲንግ ህንፃ — አይሲቲ ላብ 3',
                'building' => 'Computing Block',
                'zone' => 'academic',
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'campus_id' => $kiotCampus->id,
                'code' => 'KIT-ICT-L1',
                'name' => 'KIoT Software Engineering Lab 1',
                'name_am' => 'ኪዮት ሶፍትዌር ኢንጂነሪንግ ላብ 1',
                'building' => 'Computing Block',
                'zone' => 'academic',
                'sort_order' => 11,
                'is_active' => true,
            ],
            [
                'campus_id' => $kiotCampus->id,
                'code' => 'KIT-WS-A',
                'name' => 'Mechanical & Textile Workshop Block A',
                'name_am' => 'ሜካኒካልና ጨርቃጨርቅ ወርክሾፕ ብሎክ ኤ',
                'building' => 'Workshop Complex',
                'zone' => 'workshop',
                'sort_order' => 12,
                'is_active' => true,
            ],
            [
                'campus_id' => $kiotCampus->id,
                'code' => 'KIT-CAF',
                'name' => 'KIoT Student Dining Hall',
                'name_am' => 'ኪዮት የተማሪዎች መመገቢያ አዳራሽ',
                'building' => 'Student Amenities',
                'zone' => 'cafeteria',
                'sort_order' => 13,
                'is_active' => true,
            ],
            [
                'campus_id' => $kiotCampus->id,
                'code' => 'KIT-GATE-1',
                'name' => 'KIoT Main Gate Security Station',
                'name_am' => 'ኪዮት ዋና በር የጥበቃ ጣቢያ',
                'building' => 'Main Gatehouse',
                'zone' => 'security',
                'sort_order' => 14,
                'is_active' => true,
            ],

            // Tita Campus (Health Sciences)
            [
                'campus_id' => $titaCampus->id,
                'code' => 'TITA-MED-LH1',
                'name' => 'CMHS Lecture Hall 1 (Block T-101)',
                'name_am' => 'የህክምና ኮሌጅ የመማሪያ አዳራሽ 1 (ብሎክ ቲ-101)',
                'building' => 'Block T-101',
                'zone' => 'academic',
                'sort_order' => 15,
                'is_active' => true,
            ],
            [
                'campus_id' => $titaCampus->id,
                'code' => 'TITA-PHARM-LAB',
                'name' => 'CMHS Pharmaceutical Sciences Lab',
                'name_am' => 'የፋርማሲ ሳይንስ ላቦራቶሪ',
                'building' => 'Health Science Labs',
                'zone' => 'academic',
                'sort_order' => 16,
                'is_active' => true,
            ],
            [
                'campus_id' => $titaCampus->id,
                'code' => 'TITA-LIB',
                'name' => 'CMHS Health Sciences Library',
                'name_am' => 'የጤና ሳይንስ ቤተ-መጻሕፍት',
                'building' => 'Medical Library',
                'zone' => 'library',
                'sort_order' => 17,
                'is_active' => true,
            ],
            [
                'campus_id' => $titaCampus->id,
                'code' => 'TITA-GATE',
                'name' => 'Tita Main Gate Security Booth',
                'name_am' => 'ቲታ ዋና በር የጥበቃ ኬላ',
                'building' => 'Tita Gatehouse',
                'zone' => 'security',
                'sort_order' => 18,
                'is_active' => true,
            ],
        ];

        foreach ($locations as $locData) {
            Location::updateOrCreate(
                ['code' => $locData['code']],
                $locData
            );
        }
    }
}
