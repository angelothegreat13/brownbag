<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeTracker;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Jobs\AddMoreTimeTrackers;
use App\Jobs\CreateTimeTrackersExportFile;

use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function index()
    {   
        return view('welcome');
    }

    /**
     * Job Batching is a technique to keep track of the same kind of Jobs in collection
     * Job Batching executes each job and update the batch process  
     */
    public function export(Request $request)
    {
        // set_time_limit(12000);
    	// ini_set('memory_limit', '-1');
        $year = $request->input('year');
        $chunkSize = 10000;
        $timeTrackersCount = TimeTracker::whereYear('ttr_date', $year)->count();
        $numberOfChunks = ceil($timeTrackersCount / $chunkSize);
        $folder = now()->toDateString() . '-' . str_replace(':', '-', now()->toTimeString());

        // dispatch(new CreateTimeTrackersExportFile($chunkSize, $folder, $year));

        $batches = [
            new CreateTimeTrackersExportFile($chunkSize, $folder, $year)
        ];

        if ($timeTrackersCount > $chunkSize) {
            $numberOfChunks = $numberOfChunks - 1;
            for ($numberOfChunks; $numberOfChunks > 0; $numberOfChunks--) {
                $batches[] = new AddMoreTimeTrackers($numberOfChunks, $chunkSize, $folder, $year);
            }
        }

        Bus::batch($batches)
            ->name('Export TimeTrackers')
            ->then(function (Batch $batch) use ($folder, $year) {
                $path = "exports/{$folder}/time_trackers_{$year}.csv";
                // upload file to s3
                $file = storage_path("app/{$folder}/time_trackers.csv");
                Storage::disk('local')->put($path, file_get_contents($file));
            })
            ->catch(function (Batch $batch, \Exception $e) {
                info("Batch job failed: {$e->getMessage()}");
            })
            ->finally(function (Batch $batch) use ($folder) {
                info('The batch has finished executing');
                // delete local file (optional)
                // Storage::disk('local')->deleteDirectory($folder);
            })
            ->dispatch();

        return response()->json('Exporting TimeTrackers Data, Please Wait !!!!');
    }

}
