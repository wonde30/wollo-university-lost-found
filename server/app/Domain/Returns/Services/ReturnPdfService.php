<?php

namespace App\Domain\Returns\Services;

use App\Models\ReturnRecord;

class ReturnPdfService
{
    public function generateAcknowledgementPdf(ReturnRecord $record): string
    {
        $path = 'return-documents/' . $record->return_reference . '.pdf';
        return $path;
    }
}
