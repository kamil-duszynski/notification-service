<?php
declare(strict_types=1);

namespace App\Service\Notification\Factory;

use App\Entity\Notification;
use App\Model\Notification as NotificationModel;

class NotificationFactory
{
    public static function createFromNotificationModel(NotificationModel $notification): Notification
    {
        $channel   = $notification->getChannel();
        $message   = $notification->getMessage();
        $recipient = $notification->getRecipient();

        return (new Notification())
            ->setChannel($channel)
            ->setMessage($message)
            ->setRecipient($recipient);
    }
}