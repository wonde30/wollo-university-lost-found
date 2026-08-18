<?php

namespace DatabaseSeeders;

use AppModelsDepartment;
use IlluminateDatabaseSeeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Computer Science & Software Engineering', 'code' => 'CS-SE', 'description' => 'Department of Computing and Software Engineering'],
            ['name' => 'Electrical & Computer Engineering', 'code' => 'ECE', 'description' => 'Department of Electrical & Computer Engineering'],
            ['name' => 'Mechanical Engineering', 'code' => 'MECH', 'description' => 'Department of Mechanical Engineering'],
            ['name' => 'Civil & Environmental Engineering', 'code' => 'CIVIL', 'description' => 'Department of Civil and Water Resources Engineering'],
            ['name' => 'Medicine & Surgery', 'code' => 'MED', 'description' => 'School of Medicine & Surgery'],
            ['name' => 'Pharmacy & Biomedical', 'code' => 'PHARM', 'description' => 'School of Pharmacy and Health Sciences'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['code' => $dept['code']], [
                'name' => $dept['name'],
                'description' => $dept['description'],
                'is_active' => true,
            ]);
        }
    }
}
