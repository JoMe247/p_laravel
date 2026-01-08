<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $table = 'agency';

    // 👇 Tu primary key real
    protected $primaryKey = 'id_a';

    // 👇 Tu clave es numérica (AUTO_INCREMENT)
    public $incrementing = true;

    // 👇 Tipo de clave primaria
    protected $keyType = 'int';

    // 👇 Tu tabla NO tiene created_at ni updated_at
    public $timestamps = false;

    protected $fillable = [
        'agency_code',
        'office_phone',
        'agency_address',
        'agency_name',
        'agency_email'
    ];
}
