<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTracker extends Model
{
    use HasFactory;

    protected $guarded = ['ttr_id'];

    protected $primaryKey = 'ttr_id';

    protected $dates = ['ttr_date', 'created_at', 'updated_at', 'ttr_time_in', 'ttr_time_out', 'ttr_last_attendance_notif_sent'];

}
