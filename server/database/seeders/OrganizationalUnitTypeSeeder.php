<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrganizationalUnitType;
use App\Models\OrganizationalUnitTypeRelation;
use Illuminate\Database\Seeder;

class OrganizationalUnitTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'COLLEGE',
                'name' => 'College',
                'name_am' => 'ኮሌጅ',
                'description' => 'Academic College or Faculty within Wollo University',
                'is_root' => true,
                'is_active' => true,
            ],
            [
                'code' => 'INSTITUTE',
                'name' => 'Institute',
                'name_am' => 'ኢንስቲትዩት',
                'description' => 'Specialized Technology or Research Institute (e.g. KIoT)',
                'is_root' => true,
                'is_active' => true,
            ],
            [
                'code' => 'DIRECTORATE',
                'name' => 'Directorate',
                'name_am' => 'ዳይሬክቶሬት',
                'description' => 'Institutional Administrative Directorate (e.g. Security, Library, ICT)',
                'is_root' => true,
                'is_active' => true,
            ],
            [
                'code' => 'SCHOOL',
                'name' => 'School',
                'name_am' => 'ትምህርት ቤት',
                'description' => 'Professional School (e.g. School of Medicine)',
                'is_root' => false,
                'is_active' => true,
            ],
            [
                'code' => 'DEPARTMENT',
                'name' => 'Department',
                'name_am' => 'ክፍል',
                'description' => 'Academic Department offering degree programs',
                'is_root' => false,
                'is_active' => true,
            ],
            [
                'code' => 'OFFICE',
                'name' => 'Office / Section',
                'name_am' => 'ቢሮ / ክፍል',
                'description' => 'Administrative office, gate station, or custody vault section',
                'is_root' => false,
                'is_active' => true,
            ],
        ];

        $createdTypes = [];
        foreach ($types as $typeData) {
            $createdTypes[$typeData['code']] = OrganizationalUnitType::updateOrCreate(
                ['code' => $typeData['code']],
                $typeData
            );
        }

        // Allow Department under College
        if (isset($createdTypes['DEPARTMENT'], $createdTypes['COLLEGE'])) {
            OrganizationalUnitTypeRelation::firstOrCreate([
                'child_type_id' => $createdTypes['DEPARTMENT']->id,
                'parent_type_id' => $createdTypes['COLLEGE']->id,
                'is_active' => true,
            ]);
        }

        // Allow Department under Institute
        if (isset($createdTypes['DEPARTMENT'], $createdTypes['INSTITUTE'])) {
            OrganizationalUnitTypeRelation::firstOrCreate([
                'child_type_id' => $createdTypes['DEPARTMENT']->id,
                'parent_type_id' => $createdTypes['INSTITUTE']->id,
                'is_active' => true,
            ]);
        }

        // Allow School under College
        if (isset($createdTypes['SCHOOL'], $createdTypes['COLLEGE'])) {
            OrganizationalUnitTypeRelation::firstOrCreate([
                'child_type_id' => $createdTypes['SCHOOL']->id,
                'parent_type_id' => $createdTypes['COLLEGE']->id,
                'is_active' => true,
            ]);
        }

        // Allow Department under School
        if (isset($createdTypes['DEPARTMENT'], $createdTypes['SCHOOL'])) {
            OrganizationalUnitTypeRelation::firstOrCreate([
                'child_type_id' => $createdTypes['DEPARTMENT']->id,
                'parent_type_id' => $createdTypes['SCHOOL']->id,
                'is_active' => true,
            ]);
        }

        // Allow Office under Directorate
        if (isset($createdTypes['OFFICE'], $createdTypes['DIRECTORATE'])) {
            OrganizationalUnitTypeRelation::firstOrCreate([
                'child_type_id' => $createdTypes['OFFICE']->id,
                'parent_type_id' => $createdTypes['DIRECTORATE']->id,
                'is_active' => true,
            ]);
        }
    }
}
