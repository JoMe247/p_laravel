<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $fillable = [
        'agency',
        'created_by_type',
        'created_by_id',

        'subject',
        'assigned_type',
        'assigned_id',

        'priority',
        'status',
        'description',
        'date',
    ];

    // 🔹 Para castear fechas
    protected $casts = [
        'date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
