<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $table = 'reminders';

    protected $fillable = [
        'agency',
        'customer_id',
        'remind_at',
        'remind_to_type',
        'remind_to_id',
        'description',
        'send_email',
        'created_by_type',
        'created_by_id',
        'notified_at'
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'send_email' => 'boolean',
        'notified_at' => 'datetime'
    ];
}
