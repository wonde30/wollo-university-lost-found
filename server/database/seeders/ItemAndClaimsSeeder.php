<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\ItemStatusHistory;
use App\Models\ItemTag;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemAndClaimsSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch Users
        $admin = User::where('email', 'admin@wu.edu.et')->first();
        $dessieStaff = User::where('email', 'security.dessie@wu.edu.et')->first() ?? $admin;
        $kiotStaff = User::where('email', 'security.kiot@wu.edu.et')->first() ?? $dessieStaff;
        $titaStaff = User::where('email', 'security.tita@wu.edu.et')->first() ?? $dessieStaff;

        $studentAlemayehu = User::where('email', 'student@wu.edu.et')->first();
        $studentBethlehem = User::where('email', 'student2@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentDawit = User::where('email', 'student3@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentFatima = User::where('email', 'fatima.h@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentYohannes = User::where('email', 'yohannes.g@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentSelam = User::where('email', 'selam.t@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentMulugeta = User::where('email', 'mulugeta.b@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentHelen = User::where('email', 'helen.a@wu.edu.et')->first() ?? $studentAlemayehu;

        // Fetch Campuses
        $dessieCampus = Campus::where('short_code', 'DSS')->first();
        $kiotCampus = Campus::where('short_code', 'KIT')->first() ?? $dessieCampus;
        $titaCampus = Campus::where('short_code', 'TITA')->first() ?? $dessieCampus;

        // Fetch Categories
        $electronics = Category::where('name', 'Electronics')->first();
        $idDocs = Category::where('name', 'ID / Documents')->first();
        $clothing = Category::where('name', 'Clothing')->first();
        $books = Category::where('name', 'Books / Notes')->first();
        $keys = Category::where('name', 'Keys')->first();
        $bagWallet = Category::where('name', 'Bag / Wallet')->first();
        $jewelry = Category::where('name', 'Jewelry')->first();
        $sports = Category::where('name', 'Sports Equipment')->first();
        $glasses = Category::where('name', 'Glasses')->first();
        $umbrella = Category::where('name', 'Umbrella')->first();
        $other = Category::where('name', 'Other')->first();

        // Fetch Locations
        $locLibGf = Location::where('code', 'DSS-LIB-GF')->first();
        $locLib1f = Location::where('code', 'DSS-LIB-1F')->first();
        $locLib2f = Location::where('code', 'DSS-LIB-2F')->first();
        $locCaf1 = Location::where('code', 'DSS-CAF-1')->first();
        $locLec402 = Location::where('code', 'DSS-LEC-402')->first();
        $locLabCs = Location::where('code', 'DSS-LAB-CS')->first();
        $locStad = Location::where('code', 'DSS-STAD')->first();
        $locGate1 = Location::where('code', 'DSS-GATE-1')->first();
        $locReg = Location::where('code', 'DSS-REG')->first();
        $locKiotL3 = Location::where('code', 'KIT-ICT-L3')->first();
        $locKiotWsA = Location::where('code', 'KIT-WS-A')->first();
        $locKiotCaf = Location::where('code', 'KIT-CAF')->first();
        $locTitaMed = Location::where('code', 'TITA-MED-LH1')->first();
        $locTitaPharm = Location::where('code', 'TITA-PHARM-LAB')->first();
        $locTitaGate = Location::where('code', 'TITA-GATE')->first();

        $items = [
            // 1. WU-L000001: Lost HP Pavilion Laptop (Alemayehu)
            [
                'reference_code' => 'WU-L000001',
                'reporter_id' => $studentAlemayehu->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'lost',
                'title' => 'Lost HP Pavilion 15 Core-i7 Laptop',
                'description' => 'Silver HP Pavilion 15 laptop with black neoprene sleeve and Wollo University ICT Club sticker on lid. Lost near Main Library study table.',
                'category_id' => $electronics->id,
                'location_id' => $locLibGf?->id,
                'location_detail' => 'Study table area next to ICT Reference section, Ground Floor.',
                'brand' => 'HP',
                'color' => 'Silver',
                'serial_number' => '5CD8391XYZ',
                'incident_date' => now()->subDays(3)->toDateString(),
                'incident_time' => '14:30:00',
                'status' => 'lost',
                'held_at' => null,
                'estimated_value' => 45000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subDays(3),
                'tags' => ['laptop', 'hp', 'silver', 'pavilion', 'core-i7', 'library'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $studentAlemayehu->id, 'note' => 'Initial lost report submitted.'],
                    ['from_status' => 'reported', 'to_status' => 'lost', 'changed_by' => $dessieStaff->id, 'note' => 'Report verified and activated in public lost registry.'],
                ],
            ],

            // 2. WU-F000001: Found HP Laptop Silver (Dessie Security) -> Matches WU-L000001
            [
                'reference_code' => 'WU-F000001',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found HP 15-inch Laptop (Silver)',
                'description' => 'Silver HP laptop found unattended on library study desk 2B. Stored safely in Central Vault Cabinet A.',
                'category_id' => $electronics->id,
                'location_id' => $locLibGf?->id,
                'location_detail' => 'Main Library Study Desk 2B near reference section.',
                'brand' => 'HP',
                'color' => 'Silver',
                'serial_number' => '5CD8391XYZ',
                'incident_date' => now()->subDays(2)->toDateString(),
                'incident_time' => '17:45:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 45000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subDays(2),
                'expires_at' => now()->addDays(88),
                'tags' => ['laptop', 'hp', 'silver', 'found', 'library'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Found item logged by Library Security.'],
                    ['from_status' => 'reported', 'to_status' => 'found_unclaimed', 'changed_by' => $dessieStaff->id, 'note' => 'Transferred to Central Security Vault Cabinet A.'],
                ],
            ],

            // 3. WU-F000002: Calculus Textbook & Student ID Card (Returned with History!)
            [
                'reference_code' => 'WU-F000002',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Calculus & Analytical Geometry Textbook + Student ID',
                'description' => 'Hardcover Thomas Calculus 14th edition textbook with Wollo University student ID card inserted on front page.',
                'category_id' => $books->id,
                'location_id' => $locLec402?->id,
                'location_detail' => 'Row 4 seat 12, Block 402 Lecture Theater.',
                'brand' => 'Pearson',
                'color' => 'Blue/White',
                'incident_date' => now()->subDays(7)->toDateString(),
                'incident_time' => '11:15:00',
                'status' => 'returned',
                'held_at' => 'security_office',
                'estimated_value' => 2500.00,
                'is_high_value' => false,
                'created_at' => now()->subDays(7),
                'last_activity_at' => now()->subDays(1),
                'tags' => ['calculus', 'book', 'textbook', 'student-id', 'lecture-theater'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Handed in by janitorial staff.'],
                    ['from_status' => 'reported', 'to_status' => 'found_unclaimed', 'changed_by' => $dessieStaff->id, 'note' => 'Cataloged and shelved in Cabinet B.'],
                    ['from_status' => 'found_unclaimed', 'to_status' => 'found_claimed', 'changed_by' => $dessieStaff->id, 'note' => 'Claim submitted and approved for student Alemayehu.'],
                    ['from_status' => 'found_claimed', 'to_status' => 'returned', 'changed_by' => $dessieStaff->id, 'note' => 'Handed over in person with signed receipt.'],
                ],
            ],

            // 4. WU-L000003: Lost Samsung Galaxy A54 Phone (Bethlehem)
            [
                'reference_code' => 'WU-L000003',
                'reporter_id' => $studentBethlehem->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'lost',
                'title' => 'Lost Samsung Galaxy A54 5G (Awesome Blue)',
                'description' => 'Light blue Samsung smartphone with clear silicone case, Ethiopian flag lockscreen wallpaper, and small hairline crack at top left bezel.',
                'category_id' => $electronics->id,
                'location_id' => $locCaf1?->id,
                'location_detail' => 'Student Cafeteria Hall 1 dining table near west exit.',
                'brand' => 'Samsung',
                'color' => 'Light Blue',
                'serial_number' => 'SM-A546E/DS',
                'incident_date' => now()->subDays(2)->toDateString(),
                'incident_time' => '13:10:00',
                'status' => 'lost',
                'held_at' => null,
                'estimated_value' => 28000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subDays(2),
                'tags' => ['phone', 'samsung', 'galaxy', 'blue', 'smartphone', 'cafeteria'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $studentBethlehem->id, 'note' => 'Reported lost immediately after lunch.'],
                ],
            ],

            // 5. WU-F000003: Found Samsung Galaxy Phone (Held in Safe) -> Matches WU-L000003
            [
                'reference_code' => 'WU-F000003',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Samsung Galaxy Smartphone in Clear Case',
                'description' => 'Blue Samsung phone found on cafeteria lunch table 14. Locked with PIN pattern. Secured inside Dessie Security Heavy Safe.',
                'category_id' => $electronics->id,
                'location_id' => $locCaf1?->id,
                'location_detail' => 'Table 14, Cafeteria Hall 1.',
                'brand' => 'Samsung',
                'color' => 'Blue',
                'serial_number' => 'SM-A546E/DS',
                'incident_date' => now()->subDays(2)->toDateString(),
                'incident_time' => '13:40:00',
                'status' => 'found_claimed',
                'held_at' => 'security_office',
                'estimated_value' => 28000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subHours(5),
                'expires_at' => now()->addDays(88),
                'tags' => ['phone', 'samsung', 'blue', 'safe', 'high-value'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Turned in by student waiter.'],
                    ['from_status' => 'reported', 'to_status' => 'found_unclaimed', 'changed_by' => $dessieStaff->id, 'note' => 'Locked in Heavy Safe.'],
                    ['from_status' => 'found_unclaimed', 'to_status' => 'found_claimed', 'changed_by' => $dessieStaff->id, 'note' => 'Claim under review by Security Officer.'],
                ],
            ],

            // 6. WU-L000004: Lost Black Leather Wallet (Yohannes)
            [
                'reference_code' => 'WU-L000004',
                'reporter_id' => $studentYohannes->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'lost',
                'title' => 'Lost Black Leather Bifold Wallet (CBE ATM Card & Wollo ID)',
                'description' => 'Black genuine leather wallet containing Commercial Bank of Ethiopia debit card, Wollo University student ID card, and cash.',
                'category_id' => $bagWallet->id,
                'location_id' => $locStad?->id,
                'location_detail' => 'Concrete bleachers along eastern side of main football stadium.',
                'brand' => 'Bifold',
                'color' => 'Black',
                'incident_date' => now()->subDays(4)->toDateString(),
                'incident_time' => '16:45:00',
                'status' => 'lost',
                'held_at' => null,
                'estimated_value' => 3500.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(4),
                'tags' => ['wallet', 'leather', 'black', 'atm-card', 'stadium'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $studentYohannes->id, 'note' => 'Reported missing after intramural soccer match.'],
                ],
            ],

            // 7. WU-F000004: Found Black Leather Wallet (Dessie Security) -> Matches WU-L000004
            [
                'reference_code' => 'WU-F000004',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Black Leather Wallet with Bank Cards',
                'description' => 'Black leather wallet recovered from stadium grandstand. Contains CBE bank card and cash. Held in Cabinet B.',
                'category_id' => $bagWallet->id,
                'location_id' => $locStad?->id,
                'location_detail' => 'Eastern bleachers bench row 3.',
                'brand' => 'Leather',
                'color' => 'Black',
                'incident_date' => now()->subDays(4)->toDateString(),
                'incident_time' => '18:00:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 3500.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(4),
                'expires_at' => now()->addDays(86),
                'tags' => ['wallet', 'black', 'cbe', 'stadium', 'found'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Turned in by campus patrol officer.'],
                    ['from_status' => 'reported', 'to_status' => 'found_unclaimed', 'changed_by' => $dessieStaff->id, 'note' => 'Stored in Cabinet B.'],
                ],
            ],

            // 8. WU-F000005: Found Wollo Student ID Card for Dawit Mengistu
            [
                'reference_code' => 'WU-F000005',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Wollo University Student ID Card (Dawit Mengistu)',
                'description' => 'Plastic official student ID card for student Dawit Mengistu (ID: STUDENT-003, Medicine Dept).',
                'category_id' => $idDocs->id,
                'location_id' => $locReg?->id,
                'location_detail' => 'Registrar counter bench.',
                'brand' => 'Wollo University',
                'color' => 'White/Green',
                'incident_date' => now()->subDays(1)->toDateString(),
                'incident_time' => '09:30:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 150.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(1),
                'expires_at' => now()->addDays(89),
                'tags' => ['id-card', 'student-id', 'medicine', 'registrar'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Handed in by registrar clerk.'],
                ],
            ],

            // 9. WU-L000006: Lost Apple iPad Air 5th Gen (Fatima - KIoT)
            [
                'reference_code' => 'WU-L000006',
                'reporter_id' => $studentFatima->id,
                'campus_id' => $kiotCampus->id,
                'type' => 'lost',
                'title' => 'Lost Apple iPad Air (Space Gray, 256GB)',
                'description' => 'Space Gray 10.9-inch iPad Air in magnetic dark green smart folio case with Apple Pencil 2 attached to side magnetic strip.',
                'category_id' => $electronics->id,
                'location_id' => $locKiotL3?->id,
                'location_detail' => 'Workstation 18, Computing Building ICT Lab 3.',
                'brand' => 'Apple',
                'color' => 'Space Gray',
                'serial_number' => 'DMPX728K40',
                'incident_date' => now()->subDays(2)->toDateString(),
                'incident_time' => '15:20:00',
                'status' => 'lost',
                'held_at' => null,
                'estimated_value' => 65000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subDays(2),
                'tags' => ['ipad', 'apple', 'tablet', 'pencil', 'kiot', 'high-value'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $studentFatima->id, 'note' => 'Lost report logged with serial number.'],
                ],
            ],

            // 10. WU-F000006: Found Apple iPad Tablet (KIoT Security) -> Approved Claim with Pending Handover!
            [
                'reference_code' => 'WU-F000006',
                'reporter_id' => $kiotStaff->id,
                'campus_id' => $kiotCampus->id,
                'type' => 'found',
                'title' => 'Found Apple iPad Tablet in Green Folio',
                'description' => 'Apple iPad tablet with stylus pen found at KIoT ICT Lab 3 workstation. Stored in KIoT Locker Bay 1.',
                'category_id' => $electronics->id,
                'location_id' => $locKiotL3?->id,
                'location_detail' => 'ICT Lab 3 desk 18.',
                'brand' => 'Apple',
                'color' => 'Space Gray',
                'serial_number' => 'DMPX728K40',
                'incident_date' => now()->subDays(2)->toDateString(),
                'incident_time' => '16:00:00',
                'status' => 'found_claimed',
                'held_at' => 'security_office',
                'estimated_value' => 65000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subHours(3),
                'expires_at' => now()->addDays(88),
                'tags' => ['ipad', 'apple', 'kiot', 'claimed', 'high-value'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $kiotStaff->id, 'note' => 'Secured by Lab Assistant.'],
                    ['from_status' => 'reported', 'to_status' => 'found_unclaimed', 'changed_by' => $kiotStaff->id, 'note' => 'Vault intake at KIoT Locker Bay 1.'],
                    ['from_status' => 'found_unclaimed', 'to_status' => 'found_claimed', 'changed_by' => $kiotStaff->id, 'note' => 'Ownership verified and claim approved.'],
                ],
            ],

            // 11. WU-F000007: Casio fx-991EX Scientific Calculator (Returned with Test Token!)
            [
                'reference_code' => 'WU-F000007',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Casio fx-991EX ClassWiz Scientific Calculator',
                'description' => 'Black and white Casio advanced scientific calculator with plastic slide-on cover.',
                'category_id' => $other->id,
                'location_id' => $locLec402?->id,
                'location_detail' => 'Block 402 front row podium.',
                'brand' => 'Casio',
                'color' => 'Black/White',
                'incident_date' => now()->subDays(3)->toDateString(),
                'incident_time' => '10:00:00',
                'status' => 'returned',
                'held_at' => 'security_office',
                'estimated_value' => 2200.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(1),
                'tags' => ['calculator', 'casio', 'engineering', 'math', 'returned'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Found after morning mathematics lecture.'],
                    ['from_status' => 'reported', 'to_status' => 'returned', 'changed_by' => $dessieStaff->id, 'note' => 'Returned to student Helen Assefa with confirmation token generated.'],
                ],
            ],

            // 12. WU-L000008: Lost North Face Rain Jacket (Selam - Withdrawn by Student)
            [
                'reference_code' => 'WU-L000008',
                'reporter_id' => $studentSelam->id,
                'campus_id' => $kiotCampus->id,
                'type' => 'lost',
                'title' => 'Lost North Face Waterproof Rain Jacket (Navy Blue)',
                'description' => 'Dark navy blue windbreaker jacket with hood and white North Face logo on left chest.',
                'category_id' => $clothing->id,
                'location_id' => $locKiotCaf?->id,
                'location_detail' => 'KIoT Dining Hall jacket hanger.',
                'brand' => 'The North Face',
                'color' => 'Navy Blue',
                'incident_date' => now()->subDays(5)->toDateString(),
                'incident_time' => '18:30:00',
                'status' => 'withdrawn',
                'held_at' => null,
                'estimated_value' => 3800.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(4),
                'tags' => ['jacket', 'clothing', 'north-face', 'blue', 'withdrawn'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $studentSelam->id, 'note' => 'Reported lost after rainstorm.'],
                    ['from_status' => 'reported', 'to_status' => 'withdrawn', 'changed_by' => $studentSelam->id, 'note' => 'Withdrawn by student: Found jacket in friend dormitory room.'],
                ],
            ],

            // 13. WU-F000009: Found Key Ring with 4 Keys & Wollo Lanyard
            [
                'reference_code' => 'WU-F000009',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Keyring with 4 Keys & Wollo University Blue Lanyard',
                'description' => 'Set of 4 brass and silver keys, 1 Kingston 32GB metal USB drive, and blue Wollo University lanyard.',
                'category_id' => $keys->id,
                'location_id' => $locLib1f?->id,
                'location_detail' => 'Library 1st floor study cubicle 7.',
                'brand' => 'Kingston / Yale',
                'color' => 'Blue/Silver',
                'incident_date' => now()->subDays(1)->toDateString(),
                'incident_time' => '12:00:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 800.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(1),
                'expires_at' => now()->addDays(89),
                'tags' => ['keys', 'lanyard', 'usb', 'library', 'unclaimed'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Cataloged into Cabinet B.'],
                ],
            ],

            // 14. WU-F000010: Found Ray-Ban Sunglasses in Leather Case
            [
                'reference_code' => 'WU-F000010',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Ray-Ban Aviator Sunglasses in Brown Leather Case',
                'description' => 'Gold-framed Ray-Ban aviator sunglasses with polarized green lenses in brown snap case with cleaning cloth.',
                'category_id' => $glasses->id,
                'location_id' => $locCaf1?->id,
                'location_detail' => 'Cafeteria outdoor patio bench.',
                'brand' => 'Ray-Ban',
                'color' => 'Gold/Green',
                'incident_date' => now()->subDays(3)->toDateString(),
                'incident_time' => '15:10:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 6500.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(3),
                'expires_at' => now()->addDays(87),
                'tags' => ['glasses', 'sunglasses', 'ray-ban', 'aviator'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Logged into Vault Cabinet A.'],
                ],
            ],

            // 15. WU-F000011: Found Engineering Drawing Board & T-Square Kit (KIoT)
            [
                'reference_code' => 'WU-F000011',
                'reporter_id' => $kiotStaff->id,
                'campus_id' => $kiotCampus->id,
                'type' => 'found',
                'title' => 'Found A2 Engineering Drawing Board, T-Square & Set Squares Kit',
                'description' => 'Complete engineering drawing wooden drafting board A2 size with 60cm acrylic T-square and compass set.',
                'category_id' => $other->id,
                'location_id' => $locKiotWsA?->id,
                'location_detail' => 'Drafting Hall B, Workshop Block.',
                'brand' => 'Rotring',
                'color' => 'Wood/White',
                'incident_date' => now()->subDays(4)->toDateString(),
                'incident_time' => '17:00:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 3200.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(4),
                'expires_at' => now()->addDays(86),
                'tags' => ['drawing-board', 'engineering', 'kiot', 'rotring', 'tools'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $kiotStaff->id, 'note' => 'Stored in KIoT Bin 2.'],
                ],
            ],

            // 16. WU-F000012: Found Medical White Coat & Littmann Stethoscope (Tita Campus) -> Claim Rejected Scenario!
            [
                'reference_code' => 'WU-F000012',
                'reporter_id' => $titaStaff->id,
                'campus_id' => $titaCampus->id,
                'type' => 'found',
                'title' => 'Found Medical Doctor Lab Coat with 3M Littmann Stethoscope',
                'description' => 'White cotton clinical lab coat with embroidered Wollo CMHS crest and black 3M Littmann Classic III stethoscope.',
                'category_id' => $other->id,
                'location_id' => $locTitaMed?->id,
                'location_detail' => 'Block T-101 amphitheater front desk.',
                'brand' => '3M Littmann',
                'color' => 'White/Black',
                'serial_number' => 'LIT-891024',
                'incident_date' => now()->subDays(5)->toDateString(),
                'incident_time' => '12:30:00',
                'status' => 'found_claimed',
                'held_at' => 'security_office',
                'estimated_value' => 9500.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subDays(1),
                'expires_at' => now()->addDays(85),
                'tags' => ['stethoscope', 'lab-coat', 'medical', 'tita', 'littmann'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $titaStaff->id, 'note' => 'Logged at Tita Security Post.'],
                ],
            ],

            // 17. WU-F000013: Expiring Item 1: Found Black Automatic Umbrella (Expires in 3 Days -> Dashboard Alert!)
            [
                'reference_code' => 'WU-F000013',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Large Black Windproof Automatic Umbrella',
                'description' => 'Black double-canopy automatic open/close rain umbrella with wooden handle.',
                'category_id' => $umbrella->id,
                'location_id' => $locGate1?->id,
                'location_detail' => 'Main Gate 1 security guard rack.',
                'brand' => 'Repel',
                'color' => 'Black',
                'incident_date' => now()->subDays(87)->toDateString(),
                'incident_time' => '08:15:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 600.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(87),
                'expires_at' => now()->addDays(3), // Expiring in 3 days!
                'tags' => ['umbrella', 'black', 'expiring', 'gate-1'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Held in storage for 87 days without claim.'],
                ],
            ],

            // 18. WU-F000014: Expiring Item 2: Found Fossil Gold Watch (Expires in 5 Days -> High Value Dashboard Alert!)
            [
                'reference_code' => 'WU-F000014',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Fossil Men Stainless Steel Gold-Tone Chronograph Watch',
                'description' => 'Fossil gold-plated quartz chronograph wristwatch with metal link bracelet in good working condition.',
                'category_id' => $jewelry->id,
                'location_id' => $locCaf1?->id,
                'location_detail' => 'Faculty lounge restroom counter.',
                'brand' => 'Fossil',
                'color' => 'Gold',
                'serial_number' => 'FS5380',
                'incident_date' => now()->subDays(85)->toDateString(),
                'incident_time' => '14:00:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 12000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subDays(85),
                'expires_at' => now()->addDays(5), // Expiring in 5 days!
                'tags' => ['watch', 'fossil', 'gold', 'jewelry', 'safe', 'expiring'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Secured in Heavy Safe.'],
                ],
            ],

            // 19. WU-F000015: Disposed Item: Sports Bag with Nike Cleats (Disposed / Transferred to Sports Dept)
            [
                'reference_code' => 'WU-F000015',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Red Nike Gym Duffle Bag with Football Cleats (Size 42)',
                'description' => 'Red gym duffle bag containing used football training gear. Remained unclaimed past 90-day statutory period.',
                'category_id' => $sports->id,
                'location_id' => $locStad?->id,
                'location_detail' => 'Locker room 3.',
                'brand' => 'Nike',
                'color' => 'Red/Black',
                'incident_date' => now()->subDays(120)->toDateString(),
                'incident_time' => '17:00:00',
                'status' => 'disposed',
                'held_at' => 'security_office',
                'estimated_value' => 1800.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(15),
                'expires_at' => now()->subDays(30),
                'tags' => ['sports', 'bag', 'nike', 'football', 'disposed'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Logged 120 days ago.'],
                    ['from_status' => 'reported', 'to_status' => 'found_unclaimed', 'changed_by' => $dessieStaff->id, 'note' => 'Held in sports bin.'],
                    ['from_status' => 'found_unclaimed', 'to_status' => 'disposed', 'changed_by' => $dessieStaff->id, 'note' => 'Statutory holding period lapsed. Transferred to University Athletics Directorate.'],
                ],
            ],

            // 20. WU-L000016: Lost Lenovo ThinkPad X1 Carbon (Helen Assefa)
            [
                'reference_code' => 'WU-L000016',
                'reporter_id' => $studentHelen->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'lost',
                'title' => 'Lost Lenovo ThinkPad X1 Carbon Gen 9 Laptop (Black)',
                'description' => 'Matte black ThinkPad X1 Carbon ultrabook with red TrackPoint button and GitHub / Linux penguin stickers on lid.',
                'category_id' => $electronics->id,
                'location_id' => $locLabCs?->id,
                'location_detail' => 'CS Lab 1 row 2 workstation 8.',
                'brand' => 'Lenovo',
                'color' => 'Matte Black',
                'serial_number' => 'PF29KLM01',
                'incident_date' => now()->subDays(1)->toDateString(),
                'incident_time' => '16:00:00',
                'status' => 'lost',
                'held_at' => null,
                'estimated_value' => 75000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subDays(1),
                'tags' => ['thinkpad', 'lenovo', 'laptop', 'black', 'linux', 'cs-lab', 'high-value'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $studentHelen->id, 'note' => 'Reported lost after software engineering practical lab.'],
                ],
            ],

            // 21. WU-F000017: Found Lenovo ThinkPad Laptop (Found by Staff) -> High Match 95%!
            [
                'reference_code' => 'WU-F000017',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Lenovo ThinkPad Laptop with Stickers',
                'description' => 'Black Lenovo ThinkPad laptop with stickers found in CNCS CS Lab. Stored in Dessie Heavy Safe.',
                'category_id' => $electronics->id,
                'location_id' => $locLabCs?->id,
                'location_detail' => 'CS Lab 1 workstation.',
                'brand' => 'Lenovo',
                'color' => 'Black',
                'serial_number' => 'PF29KLM01',
                'incident_date' => now()->subDays(1)->toDateString(),
                'incident_time' => '17:30:00',
                'status' => 'found_claimed',
                'held_at' => 'security_office',
                'estimated_value' => 75000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subHours(2),
                'expires_at' => now()->addDays(89),
                'tags' => ['thinkpad', 'lenovo', 'laptop', 'black', 'safe'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Secured by CS Lab technician.'],
                    ['from_status' => 'reported', 'to_status' => 'found_unclaimed', 'changed_by' => $dessieStaff->id, 'note' => 'Placed in Vault Safe.'],
                    ['from_status' => 'found_unclaimed', 'to_status' => 'found_claimed', 'changed_by' => $dessieStaff->id, 'note' => 'Claim submitted by Helen Assefa.'],
                ],
            ],

            // 22. WU-F000018: Found Sony WH-1000XM4 Headphones (Main Library Digital Lab)
            [
                'reference_code' => 'WU-F000018',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Sony WH-1000XM4 Noise-Cancelling Headphones in Hard Case',
                'description' => 'Black over-ear wireless headphones with gold Sony branding inside zipper carrying case with audio cable and airplane adapter.',
                'category_id' => $electronics->id,
                'location_id' => $locLib2f?->id,
                'location_detail' => '2nd Floor Digital Lab booth 14.',
                'brand' => 'Sony',
                'color' => 'Black',
                'serial_number' => 'SN-549102',
                'incident_date' => now()->subDays(3)->toDateString(),
                'incident_time' => '14:00:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 22000.00,
                'is_high_value' => true,
                'last_activity_at' => now()->subDays(3),
                'expires_at' => now()->addDays(87),
                'tags' => ['sony', 'headphones', 'wireless', 'noise-cancelling', 'library'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Cataloged into Cabinet A.'],
                ],
            ],

            // 23. WU-L000019: Lost Medical Microbiology 8th Edition (Mulugeta Bekele)
            [
                'reference_code' => 'WU-L000019',
                'reporter_id' => $studentMulugeta->id,
                'campus_id' => $titaCampus->id,
                'type' => 'lost',
                'title' => 'Lost Medical Microbiology 8th Edition (Murray Textbook)',
                'description' => 'Medical Microbiology textbook with yellow highlighter marks throughout chapters 4 to 8, with name Mulugeta written on back cover.',
                'category_id' => $books->id,
                'location_id' => $locTitaPharm?->id,
                'location_detail' => 'CMHS Pharmacy Lab bench 3.',
                'brand' => 'Elsevier',
                'color' => 'Red/White',
                'incident_date' => now()->subDays(3)->toDateString(),
                'incident_time' => '11:00:00',
                'status' => 'lost',
                'held_at' => null,
                'estimated_value' => 3400.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(3),
                'tags' => ['book', 'microbiology', 'medicine', 'pharmacy', 'tita'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $studentMulugeta->id, 'note' => 'Reported lost after lab session.'],
                ],
            ],

            // 24. WU-F000020: Found Medical Microbiology Textbook (Tita Campus) -> Matches WU-L000019!
            [
                'reference_code' => 'WU-F000020',
                'reporter_id' => $titaStaff->id,
                'campus_id' => $titaCampus->id,
                'type' => 'found',
                'title' => 'Found Medical Microbiology Textbook (Murray)',
                'description' => 'Medical textbook found on pharmacy lab bench. Handed over to Tita Security Post Cabinet 1.',
                'category_id' => $books->id,
                'location_id' => $locTitaPharm?->id,
                'location_detail' => 'Lab bench 3.',
                'brand' => 'Elsevier',
                'color' => 'Red/White',
                'incident_date' => now()->subDays(3)->toDateString(),
                'incident_time' => '13:00:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 3400.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(3),
                'expires_at' => now()->addDays(87),
                'tags' => ['book', 'microbiology', 'tita', 'found'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $titaStaff->id, 'note' => 'Turned in by laboratory technician.'],
                ],
            ],

            // 25. WU-F000021: Found Ethiopian National ID Card (Fanose Abebe)
            [
                'reference_code' => 'WU-F000021',
                'reporter_id' => $dessieStaff->id,
                'campus_id' => $dessieCampus->id,
                'type' => 'found',
                'title' => 'Found Ethiopian National Digital ID Card (Fayda / Kebele)',
                'description' => 'National ID card found near Registrar office main door. Registered under name Fanose Abebe.',
                'category_id' => $idDocs->id,
                'location_id' => $locReg?->id,
                'location_detail' => 'Registrar entrance stairs.',
                'brand' => 'FDRE NID',
                'color' => 'Blue/Yellow',
                'incident_date' => now()->subDays(2)->toDateString(),
                'incident_time' => '10:15:00',
                'status' => 'found_unclaimed',
                'held_at' => 'security_office',
                'estimated_value' => 200.00,
                'is_high_value' => false,
                'last_activity_at' => now()->subDays(2),
                'expires_at' => now()->addDays(88),
                'tags' => ['national-id', 'fayda', 'id-card', 'registrar'],
                'history' => [
                    ['from_status' => null, 'to_status' => 'reported', 'changed_by' => $dessieStaff->id, 'note' => 'Cataloged into Cabinet B.'],
                ],
            ],
        ];

        foreach ($items as $itemData) {
            $tags = $itemData['tags'] ?? [];
            $history = $itemData['history'] ?? [];

            unset($itemData['tags'], $itemData['history']);

            $item = Item::updateOrCreate(
                ['reference_code' => $itemData['reference_code']],
                $itemData
            );

            // Tags
            ItemTag::where('item_id', $item->id)->delete();
            foreach ($tags as $tag) {
                ItemTag::create([
                    'item_id' => $item->id,
                    'tag' => $tag,
                ]);
            }

            // Status Histories
            ItemStatusHistory::where('item_id', $item->id)->delete();
            foreach ($history as $h) {
                ItemStatusHistory::create([
                    'item_id' => $item->id,
                    'from_status' => $h['from_status'],
                    'to_status' => $h['to_status'],
                    'changed_by' => $h['changed_by'],
                    'changed_by_role' => 'staff',
                    'note' => $h['note'],
                    'ip_address' => '127.0.0.1',
                    'created_at' => now()->subDays(2),
                ]);
            }

            // Sample Item Photo metadata
            ItemPhoto::updateOrCreate(
                ['item_id' => $item->id, 'is_primary' => true],
                [
                    'path' => 'item-photos/sample_' . strtolower($item->reference_code) . '.jpg',
                    'original_name' => strtolower($item->reference_code) . '_photo.jpg',
                    'mime_type' => 'image/jpeg',
                    'size_bytes' => 184320,
                    'width_px' => 1200,
                    'height_px' => 800,
                    'uploaded_at' => now()->subDays(2),
                ]
            );
        }
    }
}
