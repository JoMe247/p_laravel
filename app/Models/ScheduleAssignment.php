<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleAssignment extends Model
{
    protected $table = 'schedule_assignments';

    protected $fillable = [
        'agency','shift_date','target_type','target_id','shift_id',
        'assigned_by_user_id','assigned_by_sub_id',
    ];

    public function shift()
    {
        return $this->belongsTo(ScheduleShift::class, 'shift_id');
    }
}
