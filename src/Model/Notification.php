<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\User;

class Notification
{
    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly User $recipient,
        private readonly string $channel
    ) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRecipient(): User
    {
        return $this->recipient;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }
}
