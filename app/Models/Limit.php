<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Limit extends Model
{
    protected $connection = 'doc_config'; // 👈 usa la BD doc_config
    protected $table = 'limits';
    protected $primaryKey = 'id_lim';
    public $timestamps = false;

    protected $fillable = [
        'account_type',
        'msg_limit',
        'doc_limit',
        'user_limit',
    ];

    public function plan()
    {
        return $this->belongsTo(\App\Models\Limit::class, 'account_type', 'account_type');
    }
}
