<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    protected $table = 'sms';
    protected $fillable = [
        'sid','from','to','body','direction','status',
        'num_media','media_urls','date_sent','date_created'
    ];

    protected $casts = [
        'media_urls' => 'array',
        'date_sent' => 'datetime',
        'date_created' => 'datetime',
    ];
}
