<?php
declare(strict_types=1);

namespace App\Service\Notification\Provider;

use App\Model\Notification;

interface NotificationProviderInterface
{
    public function send(Notification $notification): void;

    public function getChannel(): string;
}
