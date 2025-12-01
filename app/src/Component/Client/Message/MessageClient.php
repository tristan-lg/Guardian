<?php

namespace App\Component\Client\Message;

use App\Enum\Priority;

interface MessageClient
{
    public function checkCredentials(): bool;

    public function sendMessage(array $embeds, Priority $priority): void;
}
