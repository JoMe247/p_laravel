<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sid','from','to','body','direction','status','date_sent',
        'error_code','error_message','raw'
    ];

    protected $casts = [
        'date_sent' => 'datetime',
        'raw' => 'array',
    ];

    public function getDirectionLabelAttribute(): string
    {
        return match($this->direction) {
            'inbound' => 'Entrante',
            'outbound-api', 'outbound-reply', 'outbound-call' => 'Saliente',
            default => ucfirst($this->direction),
        };
    }

    // Estado legible
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'queued' => 'En cola',
            'sent' => 'Enviado',
            'delivered' => 'Entregado',
            'undelivered' => 'No entregado',
            'failed' => 'Fallido',
            'received' => 'Recibido',
            default => ucfirst($this->status ?? 'Desconocido'),
        };
    }
}
