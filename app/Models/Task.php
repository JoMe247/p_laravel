<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Task extends Model
{
    protected $table = 'tasks';

    protected $fillable = [
        'agency',
        'subject',
        'description',
        'start_date',
        'due_date',
        'priority',
        'status',
        'assigned_type',
        'assigned_id',
        'assigned_name',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'start_date' => 'datetime',
        'due_date' => 'datetime',
    ];
}
