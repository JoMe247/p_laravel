<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'email_verified',
        'verification_token',
        'reset_token',
        'reset_token_expires',
        'role',
    ];

    protected $hidden = [
        'password_hash',
        'verification_token',
        'reset_token',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'reset_token_expires' => 'datetime',
    ];

    // Indica a Laravel que la contraseña se almacena en 'password_hash'
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
