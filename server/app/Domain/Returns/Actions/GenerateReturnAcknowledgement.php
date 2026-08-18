<?php

namespace App\Domain\Returns\Actions;

use App\Domain\Returns\Services\ReturnPdfService;
use App\Models\ReturnDocument;
use App\Models\ReturnRecord;

class GenerateReturnAcknowledgement
{
    public function __construct(protected ReturnPdfService $pdfService)
    {}

    public function execute(ReturnRecord $record): ReturnDocument
    {
        $path = $this->pdfService->generateAcknowledgementPdf($record);

        return ReturnDocument::create([
            'return_id' => $record->id,
            'document_type' => 'acknowledgement_pdf',
            'document_path' => $path,
            'generated_at' => now(),
        ]);
    }
}
