<?php
declare(strict_types=1);

namespace App\Service\Notification\Provider;

use App\Model\Notification;
use InvalidArgumentException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailProvider implements NotificationProviderInterface
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $mailerSenderEmail
    ) {}

    public function send(Notification $notification): void
    {
        $title     = $notification->getTitle();
        $message   = $notification->getMessage();
        $recipient = $notification->getRecipient();
        $to        = $recipient->getEmail();

        if (null === $to) {
            throw new InvalidArgumentException(
                "Recipient ($recipient) has not specified email address"
            );
        }

        $email = (new Email())
            ->from($this->mailerSenderEmail)
            ->to($to)
            ->subject($title)
            ->text($message);

        $this->mailer->send($email);
    }

    public function getChannel(): string
    {
        return 'email';
    }
}
