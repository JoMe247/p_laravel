<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    protected $table = 'sms';
    protected $fillable = [
        'sid','from','to','body','direction','status',
        'num_media','media_urls','date_sent','date_created', 'deleted',
    ];

    protected $casts = [
        'media_urls' => 'array',
        'date_sent' => 'datetime',
        'date_created' => 'datetime',
    ];
    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted');
    }

    /**
     * Scope: mensajes de una conversación (por número)
     */
    public function scopeConversation($query, string $phone)
    {
        return $query->where(function($q) use ($phone) {
            $q->where('from', $phone)
              ->orWhere('to', $phone);
        });
    }
    
}
