<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    protected $table = 'invoices';

    // OJO: como id es VARCHAR(36), no autoincrement
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'agency',
        'customer_id',
        'col_1',
        'col_2',
        'amount',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false; // porque created_at/updated_at son varchar
}
