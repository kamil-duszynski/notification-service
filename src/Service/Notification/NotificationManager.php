<?php
declare(strict_types=1);

namespace App\Service\Notification;

use App\Entity\User;
use App\Factory\NotificationFactory as NotificationModelFactory;
use App\Repository\NotificationRepository;
use App\Service\Notification\Factory\NotificationFactory as NotificationEntityFactory;
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
        private readonly NotificationModelFactory $notificationModelFactory,
        private readonly NotificationRepository $notificationRepository,
        private readonly LoggerInterface $logger
    ) {}

    public function addProvider(NotificationProviderInterface $provider): void
    {
        $channel = $provider->getChannel();

        if (true === array_key_exists($channel, self::$providers)) {
            return;
        }

        self::$providers[$channel] = $provider;
    }

    public function prepareChannels(array $channels): void
    {
        foreach (self::$providers as $channel => $provider) {
            if (false === in_array($channel, $channels)) {
                unset(self::$providers[$channel]);

                continue;
            }

            if (true === $channels[$channel]) {
                continue;
            }

            unset(self::$providers[$channel]);
        }
    }

    public function create(string $type, User $recipient, string $channel): self
    {
        $this->notification = $this->notificationModelFactory->create(
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
                $this->persistNotification($notification);

                // clear provider & notification after sending
                $provider           = null;
                $this->notification = null;
            } catch (Exception $exception) {
                $provider = array_values($providers)[0] ?? null;

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
                $this->notification->setChannel($channel);

                unset($providers[$channel]);
            }
        }
    }

    protected function persistNotification(Notification $notification):void
    {
        $notificationEntity = NotificationEntityFactory::createFromNotificationModel($notification);

        $this->notificationRepository->save($notificationEntity, true);
    }
}
