<?php
declare(strict_types=1);

namespace App\Model;

use App\Entity\User;

class Notification
{
    private string $channel;

    public function __construct(
        private readonly string $title,
        private readonly string $message,
        private readonly User $recipient,
        string $channel
    ) {
        $this->channel = $channel;
    }

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

    public function setChannel(string $channel): self
    {
        $this->channel = $channel;

        return $this;
    }
}
