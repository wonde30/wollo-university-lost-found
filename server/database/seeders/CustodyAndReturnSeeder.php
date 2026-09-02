<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\ReturnDocument;
use App\Models\ReturnRecord;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustodyAndReturnSeeder extends Seeder
{
    public function run(): void
    {
        $dessieStaff = User::where('email', 'security.dessie@wu.edu.et')->first() ?? User::first();
        $kiotStaff = User::where('email', 'security.kiot@wu.edu.et')->first() ?? $dessieStaff;
        $titaStaff = User::where('email', 'security.tita@wu.edu.et')->first() ?? $dessieStaff;

        $studentAlemayehu = User::where('email', 'student@wu.edu.et')->first();
        $studentHelen = User::where('email', 'helen.a@wu.edu.et')->first() ?? $studentAlemayehu;

        $cab1 = StorageLocation::where('code', 'DSS-SEC-CAB1')->first();
        $cab2 = StorageLocation::where('code', 'DSS-SEC-CAB2')->first();
        $safe = StorageLocation::where('code', 'DSS-SEC-SAFE')->first();
        $libDesk = StorageLocation::where('code', 'DSS-LIB-DESK')->first();
        $kiotBay1 = StorageLocation::where('code', 'KIT-SEC-BAY1')->first();
        $titaCab1 = StorageLocation::where('code', 'TITA-SEC-CAB1')->first();

        // 1. Custody Events for found items
        $f01 = Item::where('reference_code', 'WU-F000001')->first(); // HP Laptop
        if ($f01 && $cab1 && $libDesk) {
            CustodyEvent::create([
                'item_id' => $f01->id,
                'actor_id' => $dessieStaff->id,
                'storage_location_id' => $libDesk->id,
                'event_type' => 'intake',
                'condition' => 'good',
                'notes' => 'Received from Library Circulation Desk with power adapter.',
                'created_at' => now()->subDays(2),
            ]);
            CustodyEvent::create([
                'item_id' => $f01->id,
                'actor_id' => $dessieStaff->id,
                'storage_location_id' => $cab1->id,
                'event_type' => 'relocated',
                'condition' => 'good',
                'notes' => 'Transferred from Library holding bin to Central Vault Cabinet A.',
                'created_at' => now()->subDays(2)->addHours(2),
            ]);
        }

        $f03 = Item::where('reference_code', 'WU-F000003')->first(); // Samsung Phone
        if ($f03 && $safe) {
            CustodyEvent::create([
                'item_id' => $f03->id,
                'actor_id' => $dessieStaff->id,
                'storage_location_id' => $safe->id,
                'event_type' => 'intake',
                'condition' => 'scratched',
                'notes' => 'Intake from Cafeteria Hall 1. Hairline crack on screen noted. Locked in Heavy Safe.',
                'created_at' => now()->subDays(2),
            ]);
            CustodyEvent::create([
                'item_id' => $f03->id,
                'actor_id' => $dessieStaff->id,
                'storage_location_id' => $safe->id,
                'event_type' => 'inspected',
                'condition' => 'scratched',
                'notes' => 'Condition re-verified during claim evaluation with student Bethlehem.',
                'created_at' => now()->subHours(4),
            ]);
        }

        $f06 = Item::where('reference_code', 'WU-F000006')->first(); // iPad KIoT
        if ($f06 && $kiotBay1) {
            CustodyEvent::create([
                'item_id' => $f06->id,
                'actor_id' => $kiotStaff->id,
                'storage_location_id' => $kiotBay1->id,
                'event_type' => 'intake',
                'condition' => 'excellent',
                'notes' => 'Intake at KIoT Central Depot. Placed in Locker Bay 1.',
                'created_at' => now()->subDays(2),
            ]);
        }

        $f12 = Item::where('reference_code', 'WU-F000012')->first(); // Stethoscope Tita
        if ($f12 && $titaCab1) {
            CustodyEvent::create([
                'item_id' => $f12->id,
                'actor_id' => $titaStaff->id,
                'storage_location_id' => $titaCab1->id,
                'event_type' => 'intake',
                'condition' => 'good',
                'notes' => 'Logged at Tita Campus Security Post Cabinet 1.',
                'created_at' => now()->subDays(5),
            ]);
        }

        // 2. Returns and Return Documents (FR-44 & FR-46)

        // Return 1: Confirmed Return (WU-F000002 Calculus Book & ID)
        $itemF02 = Item::where('reference_code', 'WU-F000002')->first();
        if ($itemF02 && $studentAlemayehu) {
            $claim1 = Claim::where('item_id', $itemF02->id)->where('claimant_id', $studentAlemayehu->id)->first();
            if ($claim1) {
                $return1 = ReturnRecord::updateOrCreate(
                    ['claim_id' => $claim1->id],
                    [
                        'returned_to' => $studentAlemayehu->id,
                        'handed_over_by' => $dessieStaff->id,
                        'storage_location_id' => $cab2?->id,
                        'return_date' => now()->subDays(1)->toDateString(),
                        'return_time' => '14:30:00',
                        'condition_on_return' => 'good',
                        'notes' => 'Handed over in person at Dessie Central Security Vault. Recipient verified identity with registration slip.',
                        'recipient_confirmed' => true,
                        'confirmed_at' => now()->subDays(1)->addHours(1),
                        'confirmation_token' => 'confirmed-token-sample-wu-01',
                        'confirmation_token_expires_at' => now()->subDays(1)->addHours(48),
                    ]
                );

                ReturnDocument::updateOrCreate(
                    ['return_id' => $return1->id, 'document_type' => 'handover_receipt'],
                    [
                        'path' => 'return-documents/receipt_WU_F000002.pdf',
                        'original_name' => 'Handover_Receipt_WU_F000002.pdf',
                        'mime_type' => 'application/pdf',
                        'size_bytes' => 174080,
                        'emailed_to_student' => true,
                        'emailed_at' => now()->subDays(1)->addMinutes(10),
                        'generated_at' => now()->subDays(1),
                    ]
                );
            }
        }

        // Return 2: Active Return Pending Confirmation (WU-F000007 Casio Calculator with token test-confirm-token-wu-2026)
        $itemF07 = Item::where('reference_code', 'WU-F000007')->first();
        if ($itemF07 && $studentHelen) {
            $claim6 = Claim::where('item_id', $itemF07->id)->where('claimant_id', $studentHelen->id)->first();
            if ($claim6) {
                $return2 = ReturnRecord::updateOrCreate(
                    ['claim_id' => $claim6->id],
                    [
                        'returned_to' => $studentHelen->id,
                        'handed_over_by' => $dessieStaff->id,
                        'storage_location_id' => $cab1?->id,
                        'return_date' => now()->toDateString(),
                        'return_time' => '10:15:00',
                        'condition_on_return' => 'good',
                        'notes' => 'Handed over to Helen Assefa at Central Vault. Confirmation email dispatched.',
                        'recipient_confirmed' => false, // Active unconfirmed return to test token confirmation!
                        'confirmed_at' => null,
                        'confirmation_token' => 'test-confirm-token-wu-2026',
                        'confirmation_token_expires_at' => now()->addHours(48),
                    ]
                );

                ReturnDocument::updateOrCreate(
                    ['return_id' => $return2->id, 'document_type' => 'handover_receipt'],
                    [
                        'path' => 'return-documents/receipt_WU_F000007.pdf',
                        'original_name' => 'Handover_Receipt_WU_F000007.pdf',
                        'mime_type' => 'application/pdf',
                        'size_bytes' => 165888,
                        'emailed_to_student' => true,
                        'emailed_at' => now()->subMinutes(15),
                        'generated_at' => now(),
                    ]
                );
            }
        }
    }
}
