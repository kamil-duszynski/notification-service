<?php
declare(strict_types=1);

namespace App\Service\Notification\Provider;

use App\Model\Notification;

abstract class AbstractProvider implements NotificationProviderInterface
{
    protected function persistNotification(Notification $notification):void
    {

    }
}
