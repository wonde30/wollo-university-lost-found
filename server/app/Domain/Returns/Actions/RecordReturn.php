<?php

namespace App\Domain\Returns\Actions;

use App\Domain\Returns\DTOs\ReturnData;
use App\Domain\Returns\Services\ReturnPdfService;
use App\Domain\Returns\Services\ReturnService;
use App\Models\CustodyEvent;
use App\Models\Item;
use App\Models\ItemStatusHistory;
use App\Models\ReturnDocument;
use App\Models\ReturnRecord;
use Illuminate\Support\Facades\DB;

class RecordReturn
{
    public function __construct(
        protected ReturnService $returnService,
        protected ReturnPdfService $pdfService
    ) {}

    public function execute(ReturnData $data): ReturnRecord
    {
        return DB::transaction(function () use ($data) {
            $ref = $this->returnService->generateReference();

            $record = ReturnRecord::create([
                'return_reference' => $ref,
                'claim_id' => $data->claimId,
                'item_id' => $data->itemId,
                'user_id' => $data->userId,
                'processed_by_user_id' => $data->processedByUserId,
                'returned_at' => now(),
                'verification_method' => $data->verificationMethod,
                'notes' => $data->notes,
                'signature_path' => $data->signaturePath,
            ]);

            $item = Item::findOrFail($data->itemId);
            $item->update(['status' => 'returned']);

            ItemStatusHistory::create([
                'item_id' => $item->id,
                'changed_by_user_id' => $data->processedByUserId,
                'previous_status' => $item->status,
                'new_status' => 'returned',
                'reason' => 'Item officially returned to verified recipient',
            ]);

            CustodyEvent::create([
                'item_id' => $item->id,
                'performed_by_user_id' => $data->processedByUserId,
                'event_type' => 'checked_out',
                'notes' => 'Item returned to owner (Ref: ' . $ref . ')',
            ]);

            $pdfPath = $this->pdfService->generateAcknowledgementPdf($record);
            ReturnDocument::create([
                'return_id' => $record->id,
                'document_type' => 'acknowledgement_pdf',
                'document_path' => $pdfPath,
            ]);

            return $record;
        });
    }
}
