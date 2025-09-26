<?php
namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeneratePatientTask implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    public function uniqueId(): string
    {
        return 'generate-patient-task';
    }

    public function handle(): void
    {

    }

    public function failed(\Throwable $exception): void
    {
        logger()->channel('services')->error('GeneratePatientTask failed: ' . $exception->getMessage());
    }
}
