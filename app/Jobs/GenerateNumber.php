<?php
namespace App\Jobs;

use App\Models\NumberDate;
use App\Models\NumberMaster;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateNumber implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Queueable;

    public function uniqueId(): string
    {
        return 'generate-number';
    }

    public function handle(): void
    {
        $number = NumberDate::firstOrCreate([
            'date' => date('Y-m-d'),
        ]);

        $masters = NumberMaster::whereDate('date', date('Y-m-d'))
            ->whereNotNull('checkin')
            ->whereNull('number')
            ->orderBy('checkin', 'asc')
            ->get();

        foreach ($masters as $master) {
            $type         = $master->type;
            $set_number   = $number->$type + 1;
            $queue_number = $type . str_pad($set_number, 3, '0', STR_PAD_LEFT);

            $number->$type = $set_number;
            $number->save();

            $master->number = $queue_number;
            $master->save();
            logger()->channel('patients')->info($master->hn . ' : ' . $queue_number);
        }

        GenerateNumber::dispatch()->onQueue('number')->delay(1);
    }

    public function failed(\Throwable $exception): void
    {
        logger()->channel('services')->error('GenerateNumber failed: ' . $exception->getMessage());
    }
}
