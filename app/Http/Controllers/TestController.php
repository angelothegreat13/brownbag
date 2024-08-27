<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeTracker;
use Rap2hpoutre\FastExcel\FastExcel;
use App\Jobs\FinalizeExport;
use App\Jobs\ExportTimeTrackerChunk;

use Illuminate\Http\Request;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;

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
        set_time_limit(12000);
    	ini_set('memory_limit', '-1');

        $year = $request->input('year');
        $batchSize = 5000;
        $totalRecords = TimeTracker::whereYear('created_at', $year)->count();
        $batches = [];

        for ($i = 0; $i < $totalRecords; $i += $batchSize) {
            $start = $i + 1;
            $end = min($i + $batchSize, $totalRecords);
            $batches[] = new ExportTimeTrackerChunk($start, $end, $year);
        }

        Bus::batch($batches)
            ->then(function () use ($year) {
                info('All chunk jobs completed successfully');
                // Dispatch the finalize job after all chunks are processed
                dispatch(new FinalizeExport($year));
            })
            ->catch(function (Batch $batch, \Exception $e) {
                info("Batch job failed: {$e->getMessage()}");
            })
            ->finally(function () {
                info('The batch has finished executing');
            })
            ->dispatch();
    }

}
