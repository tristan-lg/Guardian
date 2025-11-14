<?php

namespace App\Component\Client\Message;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

interface MessageClient
{
    public function checkCredentials(): bool;

    public function sendMessage(array $embeds): void;

    public static function createClient(
        HttpClientInterface $client,
        LoggerInterface $logger,
        string $webhook,
    ): MessageClient;
}