<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\OrganizationalUnit;
use App\Models\OrganizationalUnitType;
use Illuminate\Database\Seeder;

class OrganizationalUnitSeeder extends Seeder
{
    public function run(): void
    {
        $dessieCampus = Campus::where('short_code', 'DSS')->first() ?? Campus::first();
        $kiotCampus = Campus::where('short_code', 'KIT')->first() ?? $dessieCampus;
        $titaCampus = Campus::where('short_code', 'TITA')->first() ?? $dessieCampus;

        $collegeType = OrganizationalUnitType::where('code', 'COLLEGE')->first();
        $instituteType = OrganizationalUnitType::where('code', 'INSTITUTE')->first();
        $directorateType = OrganizationalUnitType::where('code', 'DIRECTORATE')->first();
        $schoolType = OrganizationalUnitType::where('code', 'SCHOOL')->first();
        $deptType = OrganizationalUnitType::where('code', 'DEPARTMENT')->first();
        $officeType = OrganizationalUnitType::where('code', 'OFFICE')->first();

        if (! $dessieCampus || ! $collegeType || ! $deptType) {
            return;
        }

        // ==========================================
        // 1. DESSIE MAIN CAMPUS (DSS)
        // ==========================================

        // College of Natural & Computational Sciences
        $cncs = OrganizationalUnit::updateOrCreate(
            ['short_code' => 'CNCS'],
            [
                'campus_id' => $dessieCampus->id,
                'parent_id' => null,
                'type_id' => $collegeType->id,
                'name' => 'College of Natural and Computational Sciences',
                'name_am' => 'የተፈጥሮ እና ስነ-ኮምፒውተር ሳይንስ ኮሌጅ',
                'description' => 'Encompasses computer science, IT, mathematics, physics, and natural sciences.',
                'is_active' => true,
            ]
        );

        $cncsDepts = [
            ['name' => 'Computer Science', 'name_am' => 'ኮምፒውተር ሳይንስ', 'short_code' => 'CS', 'description' => 'Department of Computer Science'],
            ['name' => 'Information Technology', 'name_am' => 'ኢንፎርሜሽን ቴክኖሎጂ', 'short_code' => 'IT', 'description' => 'Department of Information Technology'],
            ['name' => 'Mathematics & Statistics', 'name_am' => 'ሂሳብና ስታትስቲክስ', 'short_code' => 'MATH-STAT', 'description' => 'Department of Mathematics and Statistics'],
            ['name' => 'Physics', 'name_am' => 'ፊዚክስ', 'short_code' => 'PHYS', 'description' => 'Department of Physics'],
            ['name' => 'Chemistry', 'name_am' => 'ኬሚስትሪ', 'short_code' => 'CHEM', 'description' => 'Department of Chemistry'],
        ];

        foreach ($cncsDepts as $dept) {
            OrganizationalUnit::updateOrCreate(
                ['short_code' => $dept['short_code']],
                [
                    'campus_id' => $dessieCampus->id,
                    'parent_id' => $cncs->id,
                    'type_id' => $deptType->id,
                    'name' => $dept['name'],
                    'name_am' => $dept['name_am'],
                    'description' => $dept['description'],
                    'is_active' => true,
                ]
            );
        }

        // College of Business and Economics
        $cbe = OrganizationalUnit::updateOrCreate(
            ['short_code' => 'CBE'],
            [
                'campus_id' => $dessieCampus->id,
                'parent_id' => null,
                'type_id' => $collegeType->id,
                'name' => 'College of Business and Economics',
                'name_am' => 'የቢዝነስና ኢኮኖሚክስ ኮሌጅ',
                'description' => 'Business, accounting, finance, and management disciplines.',
                'is_active' => true,
            ]
        );

        $cbeDepts = [
            ['name' => 'Accounting & Finance', 'name_am' => 'አካውንቲንግ እና ፋይናንስ', 'short_code' => 'ACC-FIN', 'description' => 'Department of Accounting and Finance'],
            ['name' => 'Economics', 'name_am' => 'ኢኮኖሚክስ', 'short_code' => 'ECON', 'description' => 'Department of Economics'],
            ['name' => 'Management', 'name_am' => 'ማኔጅመንት', 'short_code' => 'MGMT', 'description' => 'Department of Management'],
        ];

        foreach ($cbeDepts as $dept) {
            OrganizationalUnit::updateOrCreate(
                ['short_code' => $dept['short_code']],
                [
                    'campus_id' => $dessieCampus->id,
                    'parent_id' => $cbe->id,
                    'type_id' => $deptType->id,
                    'name' => $dept['name'],
                    'name_am' => $dept['name_am'],
                    'description' => $dept['description'],
                    'is_active' => true,
                ]
            );
        }

        // Campus Security & Safety Directorate
        if ($directorateType) {
            $secDir = OrganizationalUnit::updateOrCreate(
                ['short_code' => 'SEC-DIR'],
                [
                    'campus_id' => $dessieCampus->id,
                    'parent_id' => null,
                    'type_id' => $directorateType->id,
                    'name' => 'Campus Security & Safety Directorate',
                    'name_am' => 'የግቢ ደህንነትና ጥበቃ ዳይሬክቶሬት',
                    'description' => 'Responsible for campus security, property custody, and lost-and-found administration.',
                    'is_active' => true,
                ]
            );

            if ($officeType) {
                OrganizationalUnit::updateOrCreate(
                    ['short_code' => 'SEC-VAULT-OFF'],
                    [
                        'campus_id' => $dessieCampus->id,
                        'parent_id' => $secDir->id,
                        'type_id' => $officeType->id,
                        'name' => 'Central Lost Property & Custody Vault Office',
                        'name_am' => 'ዋና የጠፉ ንብረቶች ካዝና ቢሮ',
                        'description' => 'Central intake, secure vault storage, and verified property return desk.',
                        'is_active' => true,
                    ]
                );

                OrganizationalUnit::updateOrCreate(
                    ['short_code' => 'SEC-GATE-OFF'],
                    [
                        'campus_id' => $dessieCampus->id,
                        'parent_id' => $secDir->id,
                        'type_id' => $officeType->id,
                        'name' => 'Main Gate Security & Checkpoint Office',
                        'name_am' => 'ዋና በር የጥበቃ ቢሮ',
                        'description' => 'First point of contact for lost/found items found at campus gates.',
                        'is_active' => true,
                    ]
                );
            }

            // Library Directorate
            $libDir = OrganizationalUnit::updateOrCreate(
                ['short_code' => 'LIB-DIR'],
                [
                    'campus_id' => $dessieCampus->id,
                    'parent_id' => null,
                    'type_id' => $directorateType->id,
                    'name' => 'University Library Directorate',
                    'name_am' => 'የዩኒቨርሲቲው ቤተ-መጻሕፍት ዳይሬክቶሬት',
                    'description' => 'Manages university central and branch libraries.',
                    'is_active' => true,
                ]
            );

            if ($officeType) {
                OrganizationalUnit::updateOrCreate(
                    ['short_code' => 'LIB-CIRC-OFF'],
                    [
                        'campus_id' => $dessieCampus->id,
                        'parent_id' => $libDir->id,
                        'type_id' => $officeType->id,
                        'name' => 'Library Circulation & Lost Items Desk',
                        'name_am' => 'የቤተ-መጻሕፍት ዝውውርና የጠፉ ዕቃዎች ጠረጴዛ',
                        'description' => 'Temporary collection point for books, laptops, and IDs found in study halls.',
                        'is_active' => true,
                    ]
                );
            }
        }

        // ==========================================
        // 2. KOMBOLCHA INSTITUTE OF TECHNOLOGY (KIT)
        // ==========================================
        if ($kiotCampus && $instituteType) {
            $kiot = OrganizationalUnit::updateOrCreate(
                ['short_code' => 'KIOT-ROOT'],
                [
                    'campus_id' => $kiotCampus->id,
                    'parent_id' => null,
                    'type_id' => $instituteType->id,
                    'name' => 'Kombolcha Institute of Technology',
                    'name_am' => 'የኮምቦልቻ ቴክኖሎጂ ኢንስቲትዩት',
                    'description' => 'Engineering, computing, and technology institute of Wollo University.',
                    'is_active' => true,
                ]
            );

            $kiotDepts = [
                ['name' => 'Software Engineering', 'name_am' => 'ሶፍትዌር ኢንጂነሪንግ', 'short_code' => 'SE-KIOT', 'description' => 'Department of Software Engineering'],
                ['name' => 'Electrical & Computer Engineering', 'name_am' => 'ኤሌክትሪካልና ኮምፒውተር ኢንጂነሪንግ', 'short_code' => 'ECE-KIOT', 'description' => 'Department of Electrical & Computer Engineering'],
                ['name' => 'Mechanical Engineering', 'name_am' => 'ሜካኒካል ኢንጂነሪንግ', 'short_code' => 'MECH-KIOT', 'description' => 'Department of Mechanical Engineering'],
                ['name' => 'Civil & Environmental Engineering', 'name_am' => 'ሲቪልና አካባቢ ኢንጂነሪንግ', 'short_code' => 'CIVIL-KIOT', 'description' => 'Department of Civil Engineering'],
                ['name' => 'Textile & Garment Engineering', 'name_am' => 'ጨርቃጨርቅ ኢንጂነሪንግ', 'short_code' => 'TEXTILE-KIOT', 'description' => 'Department of Textile and Garment Engineering'],
                ['name' => 'Chemical Engineering', 'name_am' => 'ኬሚካል ኢንጂነሪንግ', 'short_code' => 'CHEM-KIOT', 'description' => 'Department of Chemical Engineering'],
            ];

            foreach ($kiotDepts as $dept) {
                OrganizationalUnit::updateOrCreate(
                    ['short_code' => $dept['short_code']],
                    [
                        'campus_id' => $kiotCampus->id,
                        'parent_id' => $kiot->id,
                        'type_id' => $deptType->id,
                        'name' => $dept['name'],
                        'name_am' => $dept['name_am'],
                        'description' => $dept['description'],
                        'is_active' => true,
                    ]
                );
            }
        }

        // ==========================================
        // 3. TITA CAMPUS - HEALTH SCIENCES (TITA)
        // ==========================================
        if ($titaCampus) {
            $cmhs = OrganizationalUnit::updateOrCreate(
                ['short_code' => 'CMHS'],
                [
                    'campus_id' => $titaCampus->id,
                    'parent_id' => null,
                    'type_id' => $collegeType->id,
                    'name' => 'College of Medicine and Health Sciences',
                    'name_am' => 'የህክምናና ጤና ሳይንስ ኮሌጅ',
                    'description' => 'Tita Health Sciences Campus covering medicine, pharmacy, nursing, and public health.',
                    'is_active' => true,
                ]
            );

            if ($schoolType) {
                $medSchool = OrganizationalUnit::updateOrCreate(
                    ['short_code' => 'MED-SCH'],
                    [
                        'campus_id' => $titaCampus->id,
                        'parent_id' => $cmhs->id,
                        'type_id' => $schoolType->id,
                        'name' => 'School of Medicine',
                        'name_am' => 'የህክምና ትምህርት ቤት',
                        'description' => 'Medical doctors and clinical specialty training.',
                        'is_active' => true,
                    ]
                );

                OrganizationalUnit::updateOrCreate(
                    ['short_code' => 'CLIN-MED'],
                    [
                        'campus_id' => $titaCampus->id,
                        'parent_id' => $medSchool->id,
                        'type_id' => $deptType->id,
                        'name' => 'Department of Clinical Medicine',
                        'name_am' => 'ክሊኒካል ህክምና ክፍል',
                        'description' => 'Internal medicine, surgery, and pediatrics department.',
                        'is_active' => true,
                    ]
                );
            }

            $healthDepts = [
                ['name' => 'Department of Pharmacy', 'name_am' => 'የፋርማሲ ክፍል', 'short_code' => 'PHARM', 'description' => 'Department of Pharmacy and Pharmaceutical Sciences'],
                ['name' => 'Department of Nursing', 'name_am' => 'የነርሲንግ ክፍል', 'short_code' => 'NURS', 'description' => 'Department of General and Comprehensive Nursing'],
                ['name' => 'Department of Public Health', 'name_am' => 'የህብረተሰብ ጤና ክፍል', 'short_code' => 'PUB-HLTH', 'description' => 'Department of Public Health & Epidemiology'],
                ['name' => 'Medical Laboratory Science', 'name_am' => 'የህክምና ላቦራቶሪ ሳይንስ', 'short_code' => 'MED-LAB', 'description' => 'Department of Medical Laboratory Sciences'],
            ];

            foreach ($healthDepts as $dept) {
                OrganizationalUnit::updateOrCreate(
                    ['short_code' => $dept['short_code']],
                    [
                        'campus_id' => $titaCampus->id,
                        'parent_id' => $cmhs->id,
                        'type_id' => $deptType->id,
                        'name' => $dept['name'],
                        'name_am' => $dept['name_am'],
                        'description' => $dept['description'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
