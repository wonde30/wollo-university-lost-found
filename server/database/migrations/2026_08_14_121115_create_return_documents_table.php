<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')
                  ->constrained('returns')
                  ->cascadeOnDelete();
            $table->enum('document_type', [
                'confirmation_pdf',    // generated Return Confirmation PDF
                'signed_receipt',      // scanned signed handover receipt
                'student_signature',   // digital signature image
                'staff_signature',     // staff member digital signature
                'condition_photo',     // photo of item at handover
            ]);
            $table->string('path', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 50);
            $table->unsignedInteger('size_bytes');
            $table->boolean('emailed_to_student')->default(false);
            $table->timestamp('emailed_at')->nullable();
            $table->timestamp('generated_at')->useCurrent();

            $table->index('return_id', 'idx_rdocs_return');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_documents');
    }
};
