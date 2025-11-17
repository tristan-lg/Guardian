<?php

namespace App\Component\Client\Message;

use App\Enum\Priority;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface MessageClient
{
    public function checkCredentials(): bool;

    public function sendMessage(array $embeds, Priority $priority): void;

    public static function createClient(
        HttpClientInterface $client,
        LoggerInterface $logger,
        string $webhook,
    ): MessageClient;
}
