<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SubUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'sub_users';

    // 🚫 No usar timestamps porque tu tabla no tiene 'updated_at'
    public $timestamps = false;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password_hash',
        'current_session_token',
        'email_verified',
        'verification_token',
        'reset_token',
        'reset_token_expires',
        'role',
        'remember_token',
        'agency',
        'twilio_number',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
        'verification_token',
        'reset_token',
    ];

    // 🔐 Para que Laravel sepa que el campo de contraseña es 'password_hash'
    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
