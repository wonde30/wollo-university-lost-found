<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\OrganizationalUnit;
use App\Models\Role;
use App\Models\User;
use App\Models\UserOrganizationalUnit;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'System Administrator', 'is_system' => true, 'is_active' => true]
        );
        $staffRole = Role::firstOrCreate(
            ['name' => 'staff'],
            ['display_name' => 'Security & Lost-and-Found Staff', 'is_system' => true, 'is_active' => true]
        );
        $studentRole = Role::firstOrCreate(
            ['name' => 'student'],
            ['display_name' => 'Student', 'is_system' => true, 'is_active' => true]
        );
        $supervisorRole = Role::firstOrCreate(
            ['name' => 'security_supervisor'],
            ['display_name' => 'Campus Security Supervisor', 'is_system' => false, 'is_active' => true]
        );
        $deptHeadRole = Role::firstOrCreate(
            ['name' => 'department_head'],
            ['display_name' => 'Academic Department Head', 'is_system' => false, 'is_active' => true]
        );

        $users = [
            // 1. Admin
            [
                'email' => 'admin@wu.edu.et',
                'role_id' => $adminRole->id,
                'full_name' => 'System Administrator',
                'university_id' => 'ADMIN-001',
                'password' => Hash::make('Admin@Wollo2026!'),
                'phone' => '+251911000001',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'gender' => 'male',
                    'home_town' => 'Dessie',
                    'emergency_contact_name' => 'ICT Directorate Support',
                    'emergency_contact_phone' => '+251331110001',
                    'bio' => 'Chief Information Systems & Governance Administrator for Wollo University Lost & Found Portal.',
                ],
                'org_unit_code' => 'CNCS',
                'role_in_unit' => 'admin',
            ],

            // 2. Staff - Dessie Main Campus
            [
                'email' => 'security.dessie@wu.edu.et',
                'role_id' => $staffRole->id,
                'full_name' => 'Abebe Bekele (Dessie Security)',
                'university_id' => 'STAFF-DESSIE-01',
                'password' => Hash::make('Staff@Dessie2026!'),
                'phone' => '+251911000002',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'gender' => 'male',
                    'home_town' => 'Dessie',
                    'emergency_contact_name' => 'Campus Police Office',
                    'emergency_contact_phone' => '+251331110002',
                    'bio' => 'Senior Custody & Vault Officer at Dessie Main Campus Security Station.',
                ],
                'org_unit_code' => 'SEC-VAULT-OFF',
                'role_in_unit' => 'custodian',
            ],

            // 3. Staff - Kombolcha Institute of Technology
            [
                'email' => 'security.kiot@wu.edu.et',
                'role_id' => $staffRole->id,
                'full_name' => 'Kassahun Tilahun (KIOT Security)',
                'university_id' => 'STAFF-KIOT-01',
                'password' => Hash::make('Staff@Dessie2026!'),
                'phone' => '+251911000003',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'gender' => 'male',
                    'home_town' => 'Kombolcha',
                    'emergency_contact_name' => 'KIoT Security Hub',
                    'emergency_contact_phone' => '+251335510002',
                    'bio' => 'Property Custody Manager at Kombolcha Institute of Technology Central Depot.',
                ],
                'org_unit_code' => 'KIOT-ROOT',
                'role_in_unit' => 'custodian',
            ],

            // 4. Staff - Tita Campus (Health Sciences)
            [
                'email' => 'security.tita@wu.edu.et',
                'role_id' => $staffRole->id,
                'full_name' => 'Mekonnen Haile (Tita Security)',
                'university_id' => 'STAFF-TITA-01',
                'password' => Hash::make('Staff@Dessie2026!'),
                'phone' => '+251911000004',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'gender' => 'male',
                    'home_town' => 'Dessie',
                    'emergency_contact_name' => 'Tita Guard Post',
                    'emergency_contact_phone' => '+251331120003',
                    'bio' => 'Property & Vault Security Officer at Tita Health Sciences Campus.',
                ],
                'org_unit_code' => 'CMHS',
                'role_in_unit' => 'custodian',
            ],

            // 5. Security Supervisor
            [
                'email' => 'supervisor.security@wu.edu.et',
                'role_id' => $supervisorRole->id,
                'full_name' => 'Commander Worku Desta',
                'university_id' => 'SUP-SEC-01',
                'password' => Hash::make('Staff@Dessie2026!'),
                'phone' => '+251911000005',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'gender' => 'male',
                    'home_town' => 'Dessie',
                    'emergency_contact_name' => 'WU Administration',
                    'emergency_contact_phone' => '+251331110000',
                    'bio' => 'Supervisor of Campus Security Operations & Institutional Property Custody Audit.',
                ],
                'org_unit_code' => 'SEC-DIR',
                'role_in_unit' => 'supervisor',
            ],

            // 6. Department Head - Computer Science (Dessie)
            [
                'email' => 'head.cs@wu.edu.et',
                'role_id' => $deptHeadRole->id,
                'full_name' => 'Dr. Mesfin Ayele',
                'university_id' => 'FAC-CS-001',
                'password' => Hash::make('Admin@Wollo2026!'),
                'phone' => '+251911000006',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'gender' => 'male',
                    'home_town' => 'Dessie',
                    'emergency_contact_name' => 'CNCS Dean Office',
                    'emergency_contact_phone' => '+251331110010',
                    'bio' => 'Associate Professor and Head of Computer Science Department, Dessie Main Campus.',
                ],
                'org_unit_code' => 'CS',
                'role_in_unit' => 'head',
            ],

            // 7. Department Head - Software Engineering (KIOT)
            [
                'email' => 'head.se@wu.edu.et',
                'role_id' => $deptHeadRole->id,
                'full_name' => 'Dr. Birhanu Kebede',
                'university_id' => 'FAC-SE-001',
                'password' => Hash::make('Admin@Wollo2026!'),
                'phone' => '+251911000007',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'gender' => 'male',
                    'home_town' => 'Kombolcha',
                    'emergency_contact_name' => 'KIoT Scientific Director Office',
                    'emergency_contact_phone' => '+251335510005',
                    'bio' => 'Head of Department of Software Engineering, Kombolcha Institute of Technology.',
                ],
                'org_unit_code' => 'SE-KIOT',
                'role_in_unit' => 'head',
            ],

            // 8. Student 1 - Alemayehu (CS Year 3, Dessie)
            [
                'email' => 'student@wu.edu.et',
                'role_id' => $studentRole->id,
                'full_name' => 'Alemayehu Tadesse',
                'university_id' => 'STUDENT-001',
                'password' => Hash::make('student@Wollo2026!'),
                'phone' => '+251911000010',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'year_of_study' => 3,
                    'gender' => 'male',
                    'home_town' => 'Dessie',
                    'emergency_contact_name' => 'Tadesse Wolde (Father)',
                    'emergency_contact_phone' => '+251911223344',
                    'bio' => '3rd Year Computer Science student passionate about web development and AI algorithms.',
                ],
                'org_unit_code' => 'CS',
                'role_in_unit' => 'student',
                'enrolled_year' => 2023,
            ],

            // 9. Student 2 - Bethlehem (SE Year 4, KIOT)
            [
                'email' => 'student2@wu.edu.et',
                'role_id' => $studentRole->id,
                'full_name' => 'Bethlehem Kebede',
                'university_id' => 'STUDENT-002',
                'password' => Hash::make('student@Wollo2026!'),
                'phone' => '+251911000011',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'year_of_study' => 4,
                    'gender' => 'female',
                    'home_town' => 'Kombolcha',
                    'emergency_contact_name' => 'Kebede Mengistu (Father)',
                    'emergency_contact_phone' => '+251911334455',
                    'bio' => 'Final Year Software Engineering student working on distributed mobile applications.',
                ],
                'org_unit_code' => 'SE-KIOT',
                'role_in_unit' => 'student',
                'enrolled_year' => 2022,
            ],

            // 10. Student 3 - Dawit (Medicine Year 2, Tita)
            [
                'email' => 'student3@wu.edu.et',
                'role_id' => $studentRole->id,
                'full_name' => 'Dawit Mengistu',
                'university_id' => 'STUDENT-003',
                'password' => Hash::make('student@Wollo2026!'),
                'phone' => '+251911000012',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'year_of_study' => 2,
                    'gender' => 'male',
                    'home_town' => 'Bahir Dar',
                    'emergency_contact_name' => 'Aster Berihun (Mother)',
                    'emergency_contact_phone' => '+251911445566',
                    'bio' => '2nd Year Medical Doctor candidate at Tita Health Sciences Campus.',
                ],
                'org_unit_code' => 'CLIN-MED',
                'role_in_unit' => 'student',
                'enrolled_year' => 2024,
            ],

            // 11. Student - Fatima (ECE Year 3, KIOT)
            [
                'email' => 'fatima.h@wu.edu.et',
                'role_id' => $studentRole->id,
                'full_name' => 'Fatima Hassan',
                'university_id' => 'WU/114520/14',
                'password' => Hash::make('student@Wollo2026!'),
                'phone' => '+251911000013',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'year_of_study' => 3,
                    'gender' => 'female',
                    'home_town' => 'Bati',
                    'emergency_contact_name' => 'Hassan Ali (Father)',
                    'emergency_contact_phone' => '+251911556677',
                    'bio' => '3rd Year Electrical and Computer Engineering student at KIoT.',
                ],
                'org_unit_code' => 'ECE-KIOT',
                'role_in_unit' => 'student',
                'enrolled_year' => 2023,
            ],

            // 12. Student - Yohannes (Accounting Year 2, Dessie)
            [
                'email' => 'yohannes.g@wu.edu.et',
                'role_id' => $studentRole->id,
                'full_name' => 'Yohannes Getachew',
                'university_id' => 'WU/116789/15',
                'password' => Hash::make('student@Wollo2026!'),
                'phone' => '+251911000014',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'year_of_study' => 2,
                    'gender' => 'male',
                    'home_town' => 'Gondar',
                    'emergency_contact_name' => 'Getachew Belay (Father)',
                    'emergency_contact_phone' => '+251911667788',
                    'bio' => '2nd Year Accounting & Finance student at CBE Dessie Campus.',
                ],
                'org_unit_code' => 'ACC-FIN',
                'role_in_unit' => 'student',
                'enrolled_year' => 2024,
            ],

            // 13. Student - Selam (Civil Eng Year 1, KIOT)
            [
                'email' => 'selam.t@wu.edu.et',
                'role_id' => $studentRole->id,
                'full_name' => 'Selam Tesfaye',
                'university_id' => 'WU/120145/16',
                'password' => Hash::make('student@Wollo2026!'),
                'phone' => '+251911000015',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'year_of_study' => 1,
                    'gender' => 'female',
                    'home_town' => 'Addis Ababa',
                    'emergency_contact_name' => 'Tesfaye Alemu (Father)',
                    'emergency_contact_phone' => '+251911778899',
                    'bio' => '1st Year Civil & Environmental Engineering student at KIoT.',
                ],
                'org_unit_code' => 'CIVIL-KIOT',
                'role_in_unit' => 'student',
                'enrolled_year' => 2025,
            ],

            // 14. Student - Mulugeta (Pharmacy Year 4, Tita)
            [
                'email' => 'mulugeta.b@wu.edu.et',
                'role_id' => $studentRole->id,
                'full_name' => 'Mulugeta Bekele',
                'university_id' => 'WU/109823/13',
                'password' => Hash::make('student@Wollo2026!'),
                'phone' => '+251911000016',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'year_of_study' => 4,
                    'gender' => 'male',
                    'home_town' => 'Hawassa',
                    'emergency_contact_name' => 'Bekele Abera (Father)',
                    'emergency_contact_phone' => '+251911889900',
                    'bio' => '4th Year Pharmacy Student at Tita Health Sciences Campus.',
                ],
                'org_unit_code' => 'PHARM',
                'role_in_unit' => 'student',
                'enrolled_year' => 2022,
            ],

            // 15. Student - Helen (IT Year 2, Dessie)
            [
                'email' => 'helen.a@wu.edu.et',
                'role_id' => $studentRole->id,
                'full_name' => 'Helen Assefa',
                'university_id' => 'WU/117890/15',
                'password' => Hash::make('student@Wollo2026!'),
                'phone' => '+251911000017',
                'language' => 'en',
                'is_active' => true,
                'profile' => [
                    'year_of_study' => 2,
                    'gender' => 'female',
                    'home_town' => 'Woldia',
                    'emergency_contact_name' => 'Assefa Negash (Father)',
                    'emergency_contact_phone' => '+251911990011',
                    'bio' => '2nd Year Information Technology student at CNCS Dessie Main Campus.',
                ],
                'org_unit_code' => 'IT',
                'role_in_unit' => 'student',
                'enrolled_year' => 2024,
            ],
        ];

        foreach ($users as $userData) {
            $profileData = $userData['profile'] ?? [];
            $orgUnitCode = $userData['org_unit_code'] ?? null;
            $roleInUnit = $userData['role_in_unit'] ?? null;
            $enrolledYear = $userData['enrolled_year'] ?? null;

            unset($userData['profile'], $userData['org_unit_code'], $userData['role_in_unit'], $userData['enrolled_year']);

            $userData['email_verified_at'] = now();

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            // User Profile
            UserProfile::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($profileData, ['user_id' => $user->id])
            );

            // User Organizational Unit link
            if ($orgUnitCode) {
                $orgUnit = OrganizationalUnit::where('short_code', $orgUnitCode)->first();
                if ($orgUnit) {
                    UserOrganizationalUnit::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'organizational_unit_id' => $orgUnit->id,
                        ],
                        [
                            'is_primary' => true,
                            'enrolled_year' => $enrolledYear,
                        ]
                    );
                }
            }
        }
    }
}
