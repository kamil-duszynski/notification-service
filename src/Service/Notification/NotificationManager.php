<?php
declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\User;
use App\Factory\NotificationFactory;
use App\Model\Notification;
use App\Service\Notification\Provider\NotificationProviderInterface;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class NotificationManager
{
    /**
     * @var array<NotificationProviderInterface>
     */
    private static array $providers = [];

    private ?Notification $notification = null;

    public function __construct(
        private readonly NotificationFactory $notificationFactory,
        private readonly LoggerInterface $logger
    ) {}

    public static function addProvider(NotificationProviderInterface $provider): void
    {
        $channel = $provider->getChannel();

        if (true === array_key_exists($channel, self::$providers)) {
            return;
        }

        self::$providers[$channel] = $provider;
    }

    public function create(string $type, User $recipient, string $channel): self
    {
        $this->notification = $this->notificationFactory->create(
            $type,
            $recipient,
            $channel
        );

        return $this;
    }

    public function send(): void
    {
        if (null === $this->notification) {
            throw new InvalidArgumentException(
                'Notification object must be created before calling this method. Use "create" method to create one.'
            );
        }

        $notification = $this->notification;
        $channel      = $notification->getChannel();

        if (false === array_key_exists($channel, self::$providers)) {
            throw new InvalidArgumentException(
                "Notification provider for requested channel ($channel) not exists."
            );
        }

        $providers = self::$providers;
        $provider  = $providers[$channel];

        unset($providers[$channel]);

        // sending with fail over
        while (null !== $provider) {
            try {
                $provider->send($notification);

                // clear provider & notification after sending
                $provider           = null;
                $this->notification = null;
            } catch (Exception $exception) {
                /** @var null|NotificationProviderInterface $provider */
                $provider = isset(array_values($providers)[0]) ?? null;

                if (null === $provider) {
                    return;
                }

                $newChannel    = $provider->getChannel();
                $noticeMessage = sprintf(
                    '%s - %s',
                    "Unable to send message through specified channel ($channel) due to an error {$exception->getMessage()}",
                    "Trying to use different communication channel: $newChannel",
                );

                $this->logger->notice($noticeMessage);

                $channel = $provider->getChannel();

                unset($providers[$channel]);
            }
        }
    }
}
