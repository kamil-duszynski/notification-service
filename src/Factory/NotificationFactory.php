<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use App\Model\Notification;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationFactory
{
    public function __construct(private readonly TranslatorInterface $translator) {}

    public function create(string $type, User $recipient, string $channel): Notification
    {
        $locale  = $recipient->getLocale();
        $config  = $this->loadConfig($type);
        $title   = $this->translator->trans(id: $config['title'], locale: $locale);
        $message = $this->translator->trans(id: $config['message'], locale: $locale);

        return new Notification(
            $title,
            $message,
            $recipient,
            $channel
        );
    }

    private function loadConfig(string $type): array
    {
        $notificationFilePath = sprintf(
            'config/notifications/%s.notification.yaml',
            $type
        );

        $config   = Yaml::parseFile($notificationFilePath, Yaml::PARSE_CONSTANT);
        $resolver = new OptionsResolver();

        $resolver->setRequired(
            [
                'title', 'message'
            ]
        );

        return $resolver->resolve($config);
    }
}
