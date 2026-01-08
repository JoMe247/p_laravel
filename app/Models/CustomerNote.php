<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\SubUser;

class CustomerNote extends Model

{

    public $timestamps = false;

    protected $table = 'customers_notes';

    protected $fillable = [
        'customer_id',
        'policy',
        'subject',
        'note',
        'created_by',
        'creator_type',
        'agency'
    ];


    // Devuelve el nombre del creador (user o sub user)
    public function getCreatorNameAttribute()
    {
        if ($this->creator_type === 'user') {
            return User::find($this->created_by)->name ?? 'Unknown';
        }

        return SubUser::find($this->created_by)->name ?? 'Unknown';
    }
}
