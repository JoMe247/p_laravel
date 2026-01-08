<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    protected $table = 'policies';

    protected $fillable = [
        'customer_id',
        'pol_carrier',
        'pol_number',
        'pol_url',
        'pol_expiration',
        'pol_eff_date',
        'pol_added_date',
        'pol_due_day',
        'pol_status',
        'pol_agent_record',
        'vehicules', 
    ];

    protected $casts = [
        'vehicules' => 'array', // Laravel lo convierte automáticamente a array/JSON
    ];
}
