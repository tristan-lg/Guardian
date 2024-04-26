<?php

namespace App\Service;

use App\Component\Client\DiscordApiClient;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordApiService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger
    ) {}

    public function getClient(string $webhook): DiscordApiClient
    {
        return DiscordApiClient::createClient($this->client, $this->logger, $webhook);
    }
}
