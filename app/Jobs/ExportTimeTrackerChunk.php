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

class ExportTimeTrackerChunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $start;
    protected $end;
    protected $year;

    /**
     * Create a new job instance.
     */
    public function __construct($start, $end, $year)
    {
        $this->start = $start;
        $this->end = $end;
        $this->year = $year;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '-1'); // No memory limit
        set_time_limit(0); // No time limit
    
        info("Starting export for records {$this->start} to {$this->end} for year {$this->year}");
    
        $data = TimeTracker::whereYear('created_at', $this->year)
                            ->offset($this->start - 1)
                            ->limit($this->end - $this->start + 1)
                            ->get();
        // info($data);
        $cacheKey = "time_tracker_chunk_{$this->start}_{$this->end}";
        
        // Store the data chunk in the cache
        cache()->put($cacheKey, $data);
    
        // Save cache keys
        $cacheKeys = cache()->get('time_tracker_chunk_keys', []);
        info('Cache keys:', $cacheKeys);

        $cacheKeys[] = $cacheKey;
        cache()->put('time_tracker_chunk_keys', $cacheKeys);
    
        info("Completed export for records {$this->start} to {$this->end} for year {$this->year}");
    }
}
