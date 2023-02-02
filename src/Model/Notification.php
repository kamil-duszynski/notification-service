<?php
declare(strict_types=1);

namespace App\Model;

class Notification
{
    public function __construct(private readonly string $title, private readonly string $message) {}

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}