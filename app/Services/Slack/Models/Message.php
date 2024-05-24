<?php

namespace App\Services\Slack\Models;

class Message
{
    private ?string $id;

    private ?string $text;

    public function __construct(?string $id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setText(string $text): Message
    {
        $this->text = $text;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }
}
