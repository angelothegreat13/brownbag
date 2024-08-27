<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeTracker;

use Illuminate\Http\Request;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;

use Rap2hpoutre\FastExcel\FastExcel;

class TestController extends Controller
{
    public function index()
    {   
        return view('welcome');
    }

    public function export(Request $request)
    {
        set_time_limit(12000);
    	ini_set('memory_limit', '-1');
        $timeTrackers = TimeTracker::all();

        return (new FastExcel($timeTrackers))->export('timeTrackers.xlsx');
    }


}
