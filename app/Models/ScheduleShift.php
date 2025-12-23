<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleShift extends Model
{
    protected $table = 'schedule_shifts';

    protected $fillable = [
        'agency','assign_type','assign_id','color',
        'is_time_off','time_off_type','time_text',
        'created_by_user_id','created_by_sub_id',
    ];
}
