<?php

namespace App\Domain\Returns\Services;

use App\Models\ReturnRecord;

class ReturnPdfService
{
    public function generateAcknowledgementPdf(ReturnRecord $record): string
    {
        $path = 'return-documents/return_' . $record->id . '_' . uniqid() . '.pdf';
        return $path;
    }
}
