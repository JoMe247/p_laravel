<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNote extends Model
{
    protected $table = 'customers_notes';

    protected $fillable = [
        'customer_id',
        'policy',
        'subject',
        'note',
        'created_by'
    ];

    public $timestamps = true; 
}

