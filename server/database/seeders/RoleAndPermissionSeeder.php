<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permission Groups
        $groups = [
            'item_management' => [
                'name' => 'item_management',
                'display_name' => 'Item Management',
                'display_name_am' => 'የዕቃዎች አስተዳደር',
                'description' => 'Item reporting, discovery cataloging, and status lifecycle operations.',
                'description_am' => 'የዕቃዎች ሪፖርት፣ ምዝገባ እና የሁኔታ ለውጥ ስራዎች።',
                'is_system' => true,
                'is_active' => true,
            ],
            'claims_management' => [
                'name' => 'claims_management',
                'display_name' => 'Claims & Ownership Verification',
                'display_name_am' => 'የባለቤትነት ጥያቄዎችና ማረጋገጫ',
                'description' => 'Ownership claim submission, evidence examination, approval, and dispute resolution.',
                'description_am' => 'የባለቤትነት ጥያቄ ማቅረብ፣ ማስረጃ መመርመር እና ውሳኔ መስጠት።',
                'is_system' => true,
                'is_active' => true,
            ],
            'custody_returns' => [
                'name' => 'custody_returns',
                'display_name' => 'Custody & Physical Returns',
                'display_name_am' => 'የንብረት ይዞታ እና ርክክብ',
                'description' => 'Vault intake, storage bin management, inter-facility transfer, and verified handover.',
                'description_am' => 'የካዝና ማከማቻ፣ የቦታ ዝውውር እና ይፋዊ የንብረት ርክክብ።',
                'is_system' => true,
                'is_active' => true,
            ],
            'user_governance' => [
                'name' => 'user_governance',
                'display_name' => 'User Accounts & Governance',
                'display_name_am' => 'የተጠቃሚዎች አስተዳደር',
                'description' => 'User account directory, status moderation, and account suspension.',
                'description_am' => 'የተጠቃሚ መለያዎችን መቆጣጠር፣ ማገድ እና ማስተዳደር።',
                'is_system' => true,
                'is_active' => true,
            ],
            'system_administration' => [
                'name' => 'system_administration',
                'display_name' => 'System Infrastructure & RBAC',
                'display_name_am' => 'የስርዓት አስተዳደር እና መዳረሻ ቁጥጥር',
                'description' => 'Campus locations, taxonomies, settings, and dynamic role/permission access control.',
                'description_am' => 'የግቢ ቦታዎች፣ ምድቦች፣ የስርዓት ቅንብሮች እና የመዳረሻ ፈቃዶች አስተዳደር።',
                'is_system' => true,
                'is_active' => true,
            ],
            'audit_reporting' => [
                'name' => 'audit_reporting',
                'display_name' => 'Audit Logs & Reporting',
                'display_name_am' => 'የኦዲት መዝገብ እና ሪፖርቶች',
                'description' => 'Immutable security event logging, institutional metrics, and data exports.',
                'description_am' => 'የደህንነት እንቅስቃሴ መዝገቦች፣ የተቋም ስታቲስቲክስ እና ሪፖርቶች።',
                'is_system' => true,
                'is_active' => true,
            ],
        ];

        $groupIdMap = [];
        foreach ($groups as $key => $groupData) {
            $group = PermissionGroup::updateOrCreate(
                ['name' => $groupData['name']],
                $groupData
            );
            $groupIdMap[$key] = $group->id;
        }

        // 2. Permissions (with permission_group_id)
        $permissions = [
            // Items Domain (item_management)
            [
                'name' => 'REPORT_LOST',
                'display_name' => 'Report Lost Property',
                'display_name_am' => 'የጠፋ ንብረት ሪፖርት ማድረግ',
                'description' => 'Submit reports for missing personal items across campus.',
                'description_am' => 'በግቢ ውስጥ የጠፉ የግል እቃዎችን ሪፖርት ማቅረብ።',
                'category' => 'items',
                'permission_group_id' => $groupIdMap['item_management'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'REPORT_FOUND',
                'display_name' => 'Report Found Property',
                'display_name_am' => 'የተገኘ ንብረት ሪፖርት ማድረግ',
                'description' => 'Register items found in classrooms, dormitories, or campus grounds.',
                'description_am' => 'በክፍል፣ በመኝታ ክፍል ወይም በግቢው ውስጥ የተገኙ እቃዎችን መመዝገብ።',
                'category' => 'items',
                'permission_group_id' => $groupIdMap['item_management'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'EDIT_OWN_ITEM',
                'display_name' => 'Edit Own Item Reports',
                'display_name_am' => 'የራስን ሪፖርት ማረም',
                'description' => 'Modify details, descriptions, and photos of personally reported items.',
                'description_am' => 'የራስዎን ሪፖርት መረጃ እና ፎቶዎች ማስተካከል።',
                'category' => 'items',
                'permission_group_id' => $groupIdMap['item_management'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'DELETE_OWN_ITEM',
                'display_name' => 'Delete Own Item Reports',
                'display_name_am' => 'የራስን ሪፖርት መሰረዝ',
                'description' => 'Withdraw or cancel personally reported property listings.',
                'description_am' => 'የራስዎን የንብረት ሪፖርት ማንሳት ወይም መሰረዝ።',
                'category' => 'items',
                'permission_group_id' => $groupIdMap['item_management'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'MANAGE_ALL_ITEMS',
                'display_name' => 'Manage All Campus Items',
                'display_name_am' => 'ሁሉንም ንብረቶች ማስተዳደር',
                'description' => 'View, edit, and moderate item reports submitted by any user.',
                'description_am' => 'በማንኛውም ተጠቃሚ የቀረቡ ሪፖርቶችን ማየት እና ማስተዳደር።',
                'category' => 'items',
                'permission_group_id' => $groupIdMap['item_management'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'CHANGE_ITEM_STATUS',
                'display_name' => 'Change Item Lifecycle Status',
                'display_name_am' => 'የንብረት ሁኔታ መቀየር',
                'description' => 'Transition items through verified, in-custody, and closed states.',
                'description_am' => 'የንብረትን ሁኔታ ማረጋገጥ እና መቀየር።',
                'category' => 'items',
                'permission_group_id' => $groupIdMap['item_management'],
                'is_system' => true,
                'is_active' => true,
            ],

            // Claims Domain (claims_management)
            [
                'name' => 'SUBMIT_CLAIM',
                'display_name' => 'Submit Ownership Claims',
                'display_name_am' => 'የባለቤትነት ጥያቄ ማቅረብ',
                'description' => 'File ownership verification claims for registered found items.',
                'description_am' => 'ለተገኙ እቃዎች የባለቤትነት ማረጋገጫ ጥያቄ ማቅረብ።',
                'category' => 'claims',
                'permission_group_id' => $groupIdMap['claims_management'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'REVIEW_CLAIMS',
                'display_name' => 'Review & Verify Claims',
                'display_name_am' => 'የባለቤትነት ጥያቄዎችን መገምገም',
                'description' => 'Examine ownership proof, approve valid claims, or reject invalid claims.',
                'description_am' => 'የባለቤትነት ማስረጃዎችን መርምሮ ማጽደቅ ወይም ውድቅ ማድረግ።',
                'category' => 'claims',
                'permission_group_id' => $groupIdMap['claims_management'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'REVERSE_CLAIMS',
                'display_name' => 'Reverse Claim Decisions',
                'display_name_am' => 'የይገባኛል ውሳኔን መቀልበስ',
                'description' => 'Reopen or reverse claim approvals upon administrative dispute.',
                'description_am' => 'አለመግባባት ሲፈጠር የይገባኛል ውሳኔዎችን እንደገና መክፈት ወይም መቀልበስ።',
                'category' => 'claims',
                'permission_group_id' => $groupIdMap['claims_management'],
                'is_system' => true,
                'is_active' => true,
            ],

            // Custody & Returns Domain (custody_returns)
            [
                'name' => 'MANAGE_CUSTODY',
                'display_name' => 'Manage Physical Custody',
                'display_name_am' => 'አካላዊ ይዞታን ማስተዳደር',
                'description' => 'Log item intake, vault storage bin placement, and inventory tracking.',
                'description_am' => 'የተገኙ እቃዎችን በደህንነት ሳጥን ውስጥ ማስቀመጥ እና መከታተል።',
                'category' => 'custody',
                'permission_group_id' => $groupIdMap['custody_returns'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'MOVE_ITEM_CUSTODY',
                'display_name' => 'Transfer Storage Location',
                'display_name_am' => 'የማስቀመጫ ቦታ ማስተላለፍ',
                'description' => 'Move items between security offices, departments, or campuses.',
                'description_am' => 'እቃዎችን በቢሮዎች፣ ክፍሎች ወይም ግቢዎች መካከል ማዛወር።',
                'category' => 'custody',
                'permission_group_id' => $groupIdMap['custody_returns'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'PROCESS_RETURNS',
                'display_name' => 'Process Property Handover',
                'display_name_am' => 'ንብረት ማስረከብን ማካሄድ',
                'description' => 'Execute identity verification and sign off physical return to claimant.',
                'description_am' => 'ማንነትን አረጋግጦ ንብረትን ለባለቤቱ ማስረከብ።',
                'category' => 'custody',
                'permission_group_id' => $groupIdMap['custody_returns'],
                'is_system' => true,
                'is_active' => true,
            ],

            // User Governance (user_governance)
            [
                'name' => 'MANAGE_USERS',
                'display_name' => 'User Directory & Roles',
                'display_name_am' => 'ተጠቃሚዎችን ማስተዳደር',
                'description' => 'View user accounts, elevate roles, and manage access privileges.',
                'description_am' => 'የተጠቃሚዎችን መለያ እና ሚናዎችን ማስተዳደር።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['user_governance'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'SUSPEND_USERS',
                'display_name' => 'Suspend User Accounts',
                'display_name_am' => 'የተጠቃሚ መለያዎችን ማገድ',
                'description' => 'Disable or reactivate student and staff accounts across the system.',
                'description_am' => 'የተጠቃሚዎችን መለያ ማገድ ወይም ማንሳት።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['user_governance'],
                'is_system' => true,
                'is_active' => true,
            ],

            // System Administration (system_administration)
            [
                'name' => 'ACCESS_ADMIN_DASHBOARD',
                'display_name' => 'Access Admin Portal',
                'display_name_am' => 'የአስተዳዳሪ ዳሽቦርድ መድረስ',
                'description' => 'Access institutional dashboards, compliance metrics, and system hubs.',
                'description_am' => 'የተቋሙን ዳሽቦርድ እና የቁጥጥር መረጃዎችን ማየት።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['system_administration'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'MANAGE_CAMPUSES',
                'display_name' => 'Campus Management',
                'display_name_am' => 'ግቢዎችን ማስተዳደር',
                'description' => 'Configure Dessie, Kombolcha, and other institutional campuses.',
                'description_am' => 'የደሴ፣ ኮምቦልቻ እና ሌሎች ግቢዎችን ማስተዳደር።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['system_administration'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'MANAGE_CATEGORIES',
                'display_name' => 'Category Taxonomy',
                'display_name_am' => 'ምድቦችን ማስተዳደር',
                'description' => 'Add, update, and order property classifications and icons.',
                'description_am' => 'የእቃ ምድቦችን ማስተዳደር።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['system_administration'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'MANAGE_LOCATIONS',
                'display_name' => 'Building & Drop Points',
                'display_name_am' => 'ቦታዎችን ማስተዳደር',
                'description' => 'Configure campus buildings, floors, rooms, and handover counters.',
                'description_am' => 'የግቢ ህንፃዎችን እና ክፍሎችን ማስተዳደር።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['system_administration'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'MANAGE_SETTINGS',
                'display_name' => 'System Settings',
                'display_name_am' => 'የስርዓት ቅንብሮችን ማስተዳደር',
                'description' => 'Configure operational parameters, retention policies, and thresholds.',
                'description_am' => 'የስርዓት ቅንብሮችን እና ፖሊሲዎችን ማስተካከል።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['system_administration'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'MANAGE_PERMISSIONS',
                'display_name' => 'Manage Dynamic RBAC',
                'display_name_am' => 'ፈቃዶችን እና ሚናዎችን ማስተዳደር',
                'description' => 'Configure role permissions and access control matrix in real-time.',
                'description_am' => 'የተጠቃሚ ሚናዎችን እና ፈቃዶችን በቀጥታ ማስተዳደር።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['system_administration'],
                'is_system' => true,
                'is_active' => true,
            ],

            // Audit & Reporting (audit_reporting)
            [
                'name' => 'VIEW_AUDIT_LOGS',
                'display_name' => 'View Security Audit Logs',
                'display_name_am' => 'የኦዲት መዝገቦችን ማየት',
                'description' => 'Inspect immutable records of actions, IP addresses, and events.',
                'description_am' => 'የስርዓት እንቅስቃሴዎችን እና የደህንነት መዝገቦችን መመልከት።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['audit_reporting'],
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'GENERATE_REPORTS',
                'display_name' => 'Generate Reports & Analytics',
                'display_name_am' => 'ሪፖርቶችን ማመንጨት',
                'description' => 'Export system-wide analytics, recovery statistics, and metrics.',
                'description_am' => 'የተቋሙን ስታቲስቲክስ እና ሪፖርቶች ማመንጨት።',
                'category' => 'admin',
                'permission_group_id' => $groupIdMap['audit_reporting'],
                'is_system' => true,
                'is_active' => true,
            ],
        ];

        foreach ($permissions as $permData) {
            Permission::updateOrCreate(
                ['name' => $permData['name']],
                $permData
            );
        }

        // Roles
        $adminRole = Role::updateOrCreate(
            ['name' => 'admin'],
            [
                'display_name' => 'System Administrator',
                'display_name_am' => 'የሲስተም አስተዳዳሪ',
                'description' => 'Full administrative access to governance, settings, RBAC, users, and audit records.',
                'description_am' => 'የስርዓቱ ሙሉ አስተዳዳሪ ቁጥጥር እና ፈቃድ ያለው።',
                'is_system' => true,
                'is_active' => true,
            ]
        );

        $staffRole = Role::updateOrCreate(
            ['name' => 'staff'],
            [
                'display_name' => 'Security & Property Staff',
                'display_name_am' => 'የደህንነት እና ንብረት ሰራተኛ',
                'description' => 'Operational custody, claim verification, physical returns, and item cataloging.',
                'description_am' => 'የንብረት ይዞታን፣ የይገባኛል ጥያቄዎችን እና ንብረት ማስረከብን የሚመራ ሰራተኛ።',
                'is_system' => true,
                'is_active' => true,
            ]
        );

        $studentRole = Role::updateOrCreate(
            ['name' => 'student'],
            [
                'display_name' => 'Student & General User',
                'display_name_am' => 'ተማሪ እና አጠቃላይ ተጠቃሚ',
                'description' => 'Standard campus citizen account capable of reporting lost/found items and filing claims.',
                'description_am' => 'የጠፋ ወይም የተገኘ ንብረት ሪፖርት ማድረግ እና የይገባኛል ጥያቄ ማቅረብ የሚችል የተማሪ መለያ።',
                'is_system' => true,
                'is_active' => true,
            ]
        );

        $supervisorRole = Role::updateOrCreate(
            ['name' => 'security_supervisor'],
            [
                'display_name' => 'Campus Security Supervisor',
                'display_name_am' => 'የግቢ ደህንነት ተቆጣጣሪ',
                'description' => 'Supervises security staff, audits vault inventories, and reviews high-value custody operations.',
                'description_am' => 'የደህንነት ሰራተኞችን የሚቆጣጠር እና የካዝና ዝውውሮችን የሚያረጋግጥ።',
                'is_system' => false,
                'is_active' => true,
            ]
        );

        $deptHeadRole = Role::updateOrCreate(
            ['name' => 'department_head'],
            [
                'display_name' => 'Academic Department Head',
                'display_name_am' => 'የትምህርት ክፍል ኃላፊ',
                'description' => 'Departmental oversight for lab equipment and academic materials lost/found in departmental spaces.',
                'description_am' => 'በክፍሉ ውስጥ ለሚጠፉ ወይም ለሚገኙ የላብራቶሪና የትምህርት እቃዎች ኃላፊነት ያለው።',
                'is_system' => false,
                'is_active' => true,
            ]
        );

        // Assign Permissions
        $allPermIds = Permission::pluck('id')->all();
        $adminRole->permissions()->sync($allPermIds);

        $supervisorPermNames = [
            'REPORT_LOST', 'REPORT_FOUND', 'EDIT_OWN_ITEM', 'DELETE_OWN_ITEM',
            'MANAGE_ALL_ITEMS', 'CHANGE_ITEM_STATUS', 'SUBMIT_CLAIM',
            'REVIEW_CLAIMS', 'REVERSE_CLAIMS', 'MANAGE_CUSTODY',
            'MOVE_ITEM_CUSTODY', 'PROCESS_RETURNS', 'VIEW_AUDIT_LOGS', 'GENERATE_REPORTS',
        ];
        $supervisorPermIds = Permission::whereIn('name', $supervisorPermNames)->pluck('id')->all();
        $supervisorRole->permissions()->sync($supervisorPermIds);

        $staffPermNames = [
            'REPORT_LOST', 'REPORT_FOUND', 'EDIT_OWN_ITEM', 'DELETE_OWN_ITEM',
            'MANAGE_ALL_ITEMS', 'CHANGE_ITEM_STATUS', 'SUBMIT_CLAIM',
            'REVIEW_CLAIMS', 'REVERSE_CLAIMS', 'MANAGE_CUSTODY',
            'MOVE_ITEM_CUSTODY', 'PROCESS_RETURNS',
        ];
        $staffPermIds = Permission::whereIn('name', $staffPermNames)->pluck('id')->all();
        $staffRole->permissions()->sync($staffPermIds);

        $deptHeadPermNames = [
            'REPORT_LOST', 'REPORT_FOUND', 'EDIT_OWN_ITEM', 'DELETE_OWN_ITEM',
            'MANAGE_ALL_ITEMS', 'SUBMIT_CLAIM', 'GENERATE_REPORTS',
        ];
        $deptHeadPermIds = Permission::whereIn('name', $deptHeadPermNames)->pluck('id')->all();
        $deptHeadRole->permissions()->sync($deptHeadPermIds);

        $studentPermNames = [
            'REPORT_LOST', 'REPORT_FOUND', 'EDIT_OWN_ITEM', 'DELETE_OWN_ITEM', 'SUBMIT_CLAIM',
        ];
        $studentPermIds = Permission::whereIn('name', $studentPermNames)->pluck('id')->all();
        $studentRole->permissions()->sync($studentPermIds);
    }
}

