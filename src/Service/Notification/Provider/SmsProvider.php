<?php
declare(strict_types=1);

namespace App\Service\Notification\Provider;

use App\Model\Notification;
use InvalidArgumentException;
use Twilio\Rest\Client;

class SmsProvider implements NotificationProviderInterface
{
    public function __construct(private readonly string $twilioNumber, private readonly Client $client) {}

    public function send(Notification $notification): void
    {
        $recipient   = $notification->getRecipient();
        $phoneNumber = $recipient->getPhoneNumber();

        if (null === $phoneNumber) {
            throw new InvalidArgumentException(
                "Recipient ($recipient) has not specified phone number"
            );
        }

        $to      = sprintf(
            '+%d%s',
            $phoneNumber->getCountryCode(),
            $phoneNumber->getNationalNumber()

        );
        $message = $notification->getMessage();

        $this->client->messages->create(
            $to,
            [
                'from' => $this->twilioNumber,
                'body' => $message,
            ]
        );
    }

    public function getChannel(): string
    {
        return 'sms';
    }
}
