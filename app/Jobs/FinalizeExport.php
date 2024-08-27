<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Rap2hpoutre\FastExcel\FastExcel;

class FinalizeExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $year;

    /**
     * Create a new job instance.
     */
    public function __construct($year)
    {
        $this->year = $year;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKeys = cache()->get('time_tracker_chunk_keys', []);
        $allData = collect();

        foreach ($cacheKeys as $key) {
            $chunkData = cache()->get($key);
            $allData = $allData->merge($chunkData);
        }

        info("Total All Data: ".count($allData));

        $filePath = storage_path("exports/time_tracker_{$this->year}.xlsx");
        (new FastExcel($allData))->export($filePath);

        // Clear the cache
        foreach ($cacheKeys as $key) {
            cache()->forget($key);
        }

        cache()->forget('time_tracker_chunk_keys');

        info("Final export completed for year {$this->year}");
    }
}
