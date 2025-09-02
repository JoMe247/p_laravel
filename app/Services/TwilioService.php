<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    public function client(): Client
    {
        return new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    public function fromNumber(): string
    {
        return config('services.twilio.whatsapp_from');
    }
}
