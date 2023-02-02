<?php
declare(strict_types=1);

namespace App\Service\Notification\Provider;

use App\Model\Notification;

class SmsProvider extends AbstractProvider
{
    public function send(Notification $notification)
    {
        // TODO: Implement send() method.
    }

    public function getChannel(): string
    {
        return 'sms';
    }
}
