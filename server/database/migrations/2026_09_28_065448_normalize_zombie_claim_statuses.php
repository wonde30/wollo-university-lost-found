<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * BUG-04 / BUG-05 fix:
     *
     * The live database contains claim records with status values ('under_review', 'reversed')
     * that no API endpoint can transition to or out of, leaving them permanently stuck.
     *
     * - 'reversed'    → 'rejected'  (the reversal already rejected the claim; 'reversed' was
     *                                a seeder artifact never implemented in the codebase)
     * - 'under_review' → 'pending'  (no endpoint transitions claims to under_review; the
     *                                orphan ReviewClaimRequest.php that declared this status
     *                                has been deleted — these are also seeder artifacts)
     *
     * Also fixes garbled pdf_footer_text in system_settings (charset corruption during seeding).
     */
    public function up(): void
    {
        // Normalise 'reversed' claims → 'rejected'
        DB::table('claims')
            ->where('status', 'reversed')
            ->update([
                'status'      => 'rejected',
                'review_note' => DB::raw("CONCAT(COALESCE(review_note, ''), ' [status normalised from reversed by migration]')"),
                'updated_at'  => now(),
            ]);

        // Record status-history entries for the normalised reversed claims
        $reversedClaims = DB::table('claim_status_histories')
            ->distinct('claim_id')
            ->where('to_status', 'reversed')
            ->pluck('claim_id');

        foreach ($reversedClaims as $claimId) {
            DB::table('claim_status_histories')->insert([
                'claim_id'        => $claimId,
                'changed_by'      => null,
                'from_status'     => 'reversed',
                'to_status'       => 'rejected',
                'changed_by_role' => 'system',
                'note'            => 'Normalised from zombie "reversed" status by migration BUG-05.',
                'ip_address'      => null,
                'created_at'      => now(),
            ]);
        }

        // Normalise 'under_review' claims → 'pending'
        DB::table('claims')
            ->where('status', 'under_review')
            ->update([
                'status'      => 'pending',
                'review_note' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'updated_at'  => now(),
            ]);

        // Record status-history entries for the normalised under_review claims
        $underReviewClaims = DB::table('claim_status_histories')
            ->distinct('claim_id')
            ->where('to_status', 'under_review')
            ->pluck('claim_id');

        foreach ($underReviewClaims as $claimId) {
            DB::table('claim_status_histories')->insert([
                'claim_id'        => $claimId,
                'changed_by'      => null,
                'from_status'     => 'under_review',
                'to_status'       => 'pending',
                'changed_by_role' => 'system',
                'note'            => 'Normalised from zombie "under_review" status by migration BUG-05.',
                'ip_address'      => null,
                'created_at'      => now(),
            ]);
        }

        // Fix garbled pdf_footer_text (charset corruption: '?' replacing '·' separator)
        DB::table('system_settings')
            ->where('key', 'pdf_footer_text')
            ->where('value', 'like', '%?%')
            ->update([
                'value'      => 'Dessie & Kombolcha Campuses · WU-LFMS Official System · Confidential',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Not reversible — data normalisation
    }
};
