<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Returns\Actions\GenerateReturnAcknowledgement;
use App\Models\ReturnRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateReturnConfirmationPdf implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ReturnRecord $record)
    {}

    public function handle(GenerateReturnAcknowledgement $acknowledgementAction): void
    {
        $acknowledgementAction->execute($this->record);
    }
}
