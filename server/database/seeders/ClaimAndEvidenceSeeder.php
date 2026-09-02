<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\ClaimEvidence;
use App\Models\ClaimStatusHistory;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClaimAndEvidenceSeeder extends Seeder
{
    public function run(): void
    {
        $dessieStaff = User::where('email', 'security.dessie@wu.edu.et')->first() ?? User::first();
        $kiotStaff = User::where('email', 'security.kiot@wu.edu.et')->first() ?? $dessieStaff;
        $titaStaff = User::where('email', 'security.tita@wu.edu.et')->first() ?? $dessieStaff;
        $supervisor = User::where('email', 'supervisor.security@wu.edu.et')->first() ?? $dessieStaff;

        $studentAlemayehu = User::where('email', 'student@wu.edu.et')->first();
        $studentBethlehem = User::where('email', 'student2@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentDawit = User::where('email', 'student3@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentFatima = User::where('email', 'fatima.h@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentYohannes = User::where('email', 'yohannes.g@wu.edu.et')->first() ?? $studentAlemayehu;
        $studentHelen = User::where('email', 'helen.a@wu.edu.et')->first() ?? $studentAlemayehu;

        $itemF02 = Item::where('reference_code', 'WU-F000002')->first();
        $itemF03 = Item::where('reference_code', 'WU-F000003')->first();
        $itemF04 = Item::where('reference_code', 'WU-F000004')->first();
        $itemF06 = Item::where('reference_code', 'WU-F000006')->first();
        $itemF07 = Item::where('reference_code', 'WU-F000007')->first();
        $itemF12 = Item::where('reference_code', 'WU-F000012')->first();
        $itemF17 = Item::where('reference_code', 'WU-F000017')->first();

        // 1. Claim on WU-F000002 (Calculus Book) -> Approved & Completed Return
        if ($itemF02 && $studentAlemayehu) {
            $claim1 = Claim::updateOrCreate(
                ['item_id' => $itemF02->id, 'claimant_id' => $studentAlemayehu->id],
                [
                    'explanation' => 'I lost my Thomas Calculus 14th edition textbook in Block 402 with my student ID card (STUDENT-001) inserted on the front cover.',
                    'status' => 'approved',
                    'reviewed_by' => $dessieStaff->id,
                    'review_note' => 'Claimant presented matching semester registration slip and student identity credentials.',
                    'reviewed_at' => now()->subDays(1),
                    'ip_address' => '127.0.0.1',
                ]
            );

            ClaimEvidence::updateOrCreate(
                ['claim_id' => $claim1->id, 'uploaded_by' => $studentAlemayehu->id],
                [
                    'evidence_type' => 'id_card',
                    'path' => 'claim-evidence/proof_reg_slip_alemayehu.pdf',
                    'original_name' => 'Registration_Slip_2026.pdf',
                    'mime_type' => 'application/pdf',
                    'size_bytes' => 245760,
                    'description' => 'Official Wollo University semester registration confirmation with student ID number.',
                    'uploaded_at' => now()->subDays(2),
                ]
            );

            ClaimStatusHistory::where('claim_id', $claim1->id)->delete();
            ClaimStatusHistory::create([
                'claim_id' => $claim1->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $studentAlemayehu->id,
                'changed_by_role' => 'student',
                'note' => 'Claim filed with proof of enrollment.',
                'created_at' => now()->subDays(2),
            ]);
            ClaimStatusHistory::create([
                'claim_id' => $claim1->id,
                'from_status' => 'pending',
                'to_status' => 'approved',
                'changed_by' => $dessieStaff->id,
                'changed_by_role' => 'staff',
                'note' => 'Verified ID and textbook notes.',
                'created_at' => now()->subDays(1),
            ]);
        }

        // 2. Claim on WU-F000003 (Samsung Phone) -> Under Review
        if ($itemF03 && $studentBethlehem) {
            $claim2 = Claim::updateOrCreate(
                ['item_id' => $itemF03->id, 'claimant_id' => $studentBethlehem->id],
                [
                    'explanation' => 'Light blue Samsung Galaxy A54 in clear case. Has a small crack on upper left screen bezel and Ethiopian flag wallpaper. I can unlock it with my PIN code.',
                    'status' => 'under_review',
                    'reviewed_by' => $dessieStaff->id,
                    'review_note' => 'Visual description matches phone in vault. Claimant invited to Security Office for live PIN entry verification.',
                    'reviewed_at' => now()->subHours(4),
                    'ip_address' => '127.0.0.1',
                ]
            );

            ClaimEvidence::updateOrCreate(
                ['claim_id' => $claim2->id, 'uploaded_by' => $studentBethlehem->id],
                [
                    'evidence_type' => 'receipt',
                    'path' => 'claim-evidence/proof_phone_receipt_bethlehem.jpg',
                    'original_name' => 'tele_mobile_purchase_receipt.jpg',
                    'mime_type' => 'image/jpeg',
                    'size_bytes' => 412000,
                    'description' => 'Original store purchase receipt showing IMEI match SM-A546E/DS.',
                    'uploaded_at' => now()->subHours(6),
                ]
            );

            ClaimStatusHistory::where('claim_id', $claim2->id)->delete();
            ClaimStatusHistory::create([
                'claim_id' => $claim2->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $studentBethlehem->id,
                'changed_by_role' => 'student',
                'note' => 'Claim submitted with purchase receipt.',
                'created_at' => now()->subHours(6),
            ]);
            ClaimStatusHistory::create([
                'claim_id' => $claim2->id,
                'from_status' => 'pending',
                'to_status' => 'under_review',
                'changed_by' => $dessieStaff->id,
                'changed_by_role' => 'staff',
                'note' => 'Initiated verification and scheduled PIN check.',
                'created_at' => now()->subHours(4),
            ]);
        }

        // 3. Claim on WU-F000006 (Apple iPad KIoT) -> Approved
        if ($itemF06 && $studentFatima) {
            $claim3 = Claim::updateOrCreate(
                ['item_id' => $itemF06->id, 'claimant_id' => $studentFatima->id],
                [
                    'explanation' => 'Space Gray Apple iPad Air 5th Gen with dark green folio and Apple Pencil 2. Serial number is DMPX728K40 and linked to my university email fatima.h@wu.edu.et.',
                    'status' => 'approved',
                    'reviewed_by' => $kiotStaff->id,
                    'review_note' => 'Serial number and Apple ID ownership confirmed. Approved for collection at KIoT Depot Locker Bay 1.',
                    'reviewed_at' => now()->subHours(2),
                    'ip_address' => '127.0.0.1',
                ]
            );

            ClaimEvidence::updateOrCreate(
                ['claim_id' => $claim3->id, 'uploaded_by' => $studentFatima->id],
                [
                    'evidence_type' => 'serial_proof',
                    'path' => 'claim-evidence/proof_ipad_serial_fatima.png',
                    'original_name' => 'apple_support_profile_screenshot.png',
                    'mime_type' => 'image/png',
                    'size_bytes' => 318000,
                    'description' => 'Screenshot of Apple ID device registry confirming matching serial number DMPX728K40.',
                    'uploaded_at' => now()->subHours(3),
                ]
            );

            ClaimStatusHistory::where('claim_id', $claim3->id)->delete();
            ClaimStatusHistory::create([
                'claim_id' => $claim3->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $studentFatima->id,
                'changed_by_role' => 'student',
                'note' => 'Claim submitted with serial number verification.',
                'created_at' => now()->subHours(3),
            ]);
            ClaimStatusHistory::create([
                'claim_id' => $claim3->id,
                'from_status' => 'pending',
                'to_status' => 'approved',
                'changed_by' => $kiotStaff->id,
                'changed_by_role' => 'staff',
                'note' => 'Approved by KIoT Security Custodian.',
                'created_at' => now()->subHours(2),
            ]);
        }

        // 4. Claim on WU-F000017 (ThinkPad Laptop) -> Pending Review
        if ($itemF17 && $studentHelen) {
            $claim4 = Claim::updateOrCreate(
                ['item_id' => $itemF17->id, 'claimant_id' => $studentHelen->id],
                [
                    'explanation' => 'Lenovo ThinkPad X1 Carbon ultrabook. It has a Linux penguin sticker on the lid and Wollo CS Club logo. Serial number PF29KLM01.',
                    'status' => 'pending',
                    'reviewed_by' => null,
                    'review_note' => null,
                    'reviewed_at' => null,
                    'ip_address' => '127.0.0.1',
                ]
            );

            ClaimEvidence::updateOrCreate(
                ['claim_id' => $claim4->id, 'uploaded_by' => $studentHelen->id],
                [
                    'evidence_type' => 'serial_proof',
                    'path' => 'claim-evidence/proof_lenovo_warranty_helen.pdf',
                    'original_name' => 'Lenovo_Warranty_Document.pdf',
                    'mime_type' => 'application/pdf',
                    'size_bytes' => 198000,
                    'description' => 'Lenovo Vantage device specification and serial number document.',
                    'uploaded_at' => now()->subHours(1),
                ]
            );

            ClaimStatusHistory::where('claim_id', $claim4->id)->delete();
            ClaimStatusHistory::create([
                'claim_id' => $claim4->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $studentHelen->id,
                'changed_by_role' => 'student',
                'note' => 'New claim submitted, awaiting staff review.',
                'created_at' => now()->subHours(1),
            ]);
        }

        // 5. Claim on WU-F000012 (Medical Coat & Stethoscope) -> Rejected
        if ($itemF12 && $studentYohannes) {
            $claim5 = Claim::updateOrCreate(
                ['item_id' => $itemF12->id, 'claimant_id' => $studentYohannes->id],
                [
                    'explanation' => 'I left my lab coat and Littmann stethoscope in the amphitheater during afternoon class.',
                    'status' => 'rejected',
                    'reviewed_by' => $titaStaff->id,
                    'review_note' => 'Claimant is enrolled in Accounting at Dessie Campus and does not attend Tita Health Sciences courses. Failed to identify stethoscope engraving.',
                    'reviewed_at' => now()->subDays(1),
                    'ip_address' => '127.0.0.1',
                ]
            );

            ClaimStatusHistory::where('claim_id', $claim5->id)->delete();
            ClaimStatusHistory::create([
                'claim_id' => $claim5->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $studentYohannes->id,
                'changed_by_role' => 'student',
                'note' => 'Claim filed.',
                'created_at' => now()->subDays(2),
            ]);
            ClaimStatusHistory::create([
                'claim_id' => $claim5->id,
                'from_status' => 'pending',
                'to_status' => 'rejected',
                'changed_by' => $titaStaff->id,
                'changed_by_role' => 'staff',
                'note' => 'Rejected due to failed identity & department verification.',
                'created_at' => now()->subDays(1),
            ]);
        }

        // 6. Claim on WU-F000007 (Casio Calculator) -> Approved
        if ($itemF07 && $studentHelen) {
            $claim6 = Claim::updateOrCreate(
                ['item_id' => $itemF07->id, 'claimant_id' => $studentHelen->id],
                [
                    'explanation' => 'Casio fx-991EX calculator with initials H.A. carved inside the slide cover.',
                    'status' => 'approved',
                    'reviewed_by' => $dessieStaff->id,
                    'review_note' => 'Slide cover initials H.A. verified.',
                    'reviewed_at' => now()->subDays(1),
                    'ip_address' => '127.0.0.1',
                ]
            );

            ClaimStatusHistory::where('claim_id', $claim6->id)->delete();
            ClaimStatusHistory::create([
                'claim_id' => $claim6->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $studentHelen->id,
                'changed_by_role' => 'student',
                'note' => 'Claim submitted.',
                'created_at' => now()->subDays(2),
            ]);
            ClaimStatusHistory::create([
                'claim_id' => $claim6->id,
                'from_status' => 'pending',
                'to_status' => 'approved',
                'changed_by' => $dessieStaff->id,
                'changed_by_role' => 'staff',
                'note' => 'Initials matched. Return processed.',
                'created_at' => now()->subDays(1),
            ]);
        }

        // 7. Claim on WU-F000004 (Wallet) -> Reversed
        if ($itemF04 && $studentDawit) {
            $claim7 = Claim::updateOrCreate(
                ['item_id' => $itemF04->id, 'claimant_id' => $studentDawit->id],
                [
                    'explanation' => 'Black leather wallet lost at stadium.',
                    'status' => 'reversed',
                    'reviewed_by' => $supervisor->id,
                    'review_note' => 'Initial rejection reversed by Security Supervisor following supplementary SMS bank notification proof.',
                    'reviewed_at' => now()->subHours(8),
                    'ip_address' => '127.0.0.1',
                ]
            );

            ClaimStatusHistory::where('claim_id', $claim7->id)->delete();
            ClaimStatusHistory::create([
                'claim_id' => $claim7->id,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => $studentDawit->id,
                'changed_by_role' => 'student',
                'note' => 'Initial claim.',
                'created_at' => now()->subDays(3),
            ]);
            ClaimStatusHistory::create([
                'claim_id' => $claim7->id,
                'from_status' => 'pending',
                'to_status' => 'rejected',
                'changed_by' => $dessieStaff->id,
                'changed_by_role' => 'staff',
                'note' => 'Rejected initially for insufficient proof.',
                'created_at' => now()->subDays(2),
            ]);
            ClaimStatusHistory::create([
                'claim_id' => $claim7->id,
                'from_status' => 'rejected',
                'to_status' => 'reversed',
                'changed_by' => $supervisor->id,
                'changed_by_role' => 'security_supervisor',
                'note' => 'Supervisor reviewed dispute and reversed decision based on bank SMS receipt.',
                'created_at' => now()->subHours(8),
            ]);
        }
    }
}
