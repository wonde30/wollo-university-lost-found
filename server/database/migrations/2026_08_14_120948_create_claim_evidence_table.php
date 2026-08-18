<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')
                  ->constrained('claims')
                  ->cascadeOnDelete();
            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->restrictOnDelete();
            $table->enum('evidence_type', [
                'photo',        // photo of receipt, packaging, ID with item
                'document',     // scanned receipt, warranty card
                'purchase_proof', // screenshot of purchase record
                'other',
            ]);
            $table->string('path', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 50);
            $table->unsignedInteger('size_bytes');
            $table->string('description', 255)->nullable(); // claimant explains what proof this is
            $table->timestamp('uploaded_at')->useCurrent();

            $table->index('claim_id', 'idx_evidence_claim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_evidence');
    }
};
