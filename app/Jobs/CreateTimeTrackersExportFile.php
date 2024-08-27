<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\TimeTracker;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Storage;

class CreateTimeTrackersExportFile implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chunkSize;
    protected $folder;
    protected $year;

    /**
     * Create a new job instance.
     */
    public function __construct($chunkSize, $folder, $year)
    {
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
            ->take($this->chunkSize)
            ->get();

        Storage::disk('local')->makeDirectory($this->folder);

        (new FastExcel($this->timeTrackersGenerator($timeTrackers)))
            ->export(storage_path("app/{$this->folder}/time_trackers.csv"));
    }

    private function timeTrackersGenerator($timeTrackers)
    {
        foreach ($timeTrackers as $timeTracker) {
            yield $timeTracker;
        }
    }

}
