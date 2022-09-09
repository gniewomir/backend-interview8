<?php

namespace App\Messenger\Message;

class EmailMessage
{
    public function __construct(private readonly int $id)
    {

    }

    public function getId(): int
    {
        return $this->id;
    }
}
