<?php
declare(strict_types=1);

namespace App\Service\Notification\Provider;

use App\Model\Notification;

class EmailProvider implements NotificationProviderInterface
{
    public function send(Notification $notification): void
    {
    }

    public function getChannel(): string
    {
        return 'email';
    }
}
