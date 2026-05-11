<?php

namespace App\Services\Push;

use App\Models\VapidKeySet;
use Minishlink\WebPush\WebPush;

class WebPushClientFactory
{
    public function make(VapidKeySet $vapidKeySet): WebPush
    {
        return new WebPush(
            [
                'VAPID' => [
                    'subject' => config('core.web_push.vapid_subject'),
                    'publicKey' => $vapidKeySet->public_key,
                    'privateKey' => $vapidKeySet->private_key_encrypted,
                ],
            ],
            [
                'TTL' => config('core.web_push.ttl'),
                'urgency' => config('core.web_push.urgency'),
                'batchSize' => config('core.web_push.batch_size'),
            ],
            config('core.web_push.timeout'),
        );
    }
}
