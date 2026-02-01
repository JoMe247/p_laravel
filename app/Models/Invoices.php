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
        'next_py_date',
        'creation_date',
        'payment_date',
        'inv_prices',
        'created_at',
        'updated_at',

        'fee',
        'fee_split',
        'fee_payment1_method',
        'fee_payment1_value',
        'fee_payment2_method',
        'fee_payment2_value',

        'premium',
        'premium_split',
        'premium_payment1_method',
        'premium_payment1_value',
        'premium_payment2_method',
        'premium_payment2_value',

        'policy_number',
        'invoice_number',


    ];

    public $timestamps = false; // porque created_at/updated_at son varchar
}
