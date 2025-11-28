<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $table = 'calendar_events';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'notification_value',
        'notification_unit',
        'color',
        'is_public'
    ];
}
