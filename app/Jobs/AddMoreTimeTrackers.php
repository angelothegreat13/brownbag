<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Rap2hpoutre\FastExcel\FastExcel;
use App\Models\TimeTracker;

class AddMoreTimeTrackers implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chunkIndex;
    protected $chunkSize;
    protected $folder;
    protected $year;

    /**
     * Create a new job instance.
     */
    public function __construct($chunkIndex, $chunkSize, $folder, $year)
    {
        $this->chunkIndex = $chunkIndex;
        $this->chunkSize = $chunkSize;
        $this->folder = $folder;
        $this->year = $year;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $timeTrackers = TimeTracker::whereYear('ttr_date', $this->year)
            ->skip($this->chunkIndex * $this->chunkSize)
            ->take($this->chunkSize)
            ->get()
            ->toArray();

        $file = storage_path("app/{$this->folder}/time_trackers.csv");
        $open = fopen($file, 'a+');

        foreach ($timeTrackers as $timeTracker) {
            fputcsv($open, $timeTracker);
        }
        
        fclose($open);

        info("TimeTracker Export completed for year {$this->year}");
    }
}
