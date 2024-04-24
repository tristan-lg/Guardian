<?php

namespace App\Service;

use App\Component\Client\DiscordApiClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordApiService
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {}

    public function getClient(string $webhook): DiscordApiClient
    {
        return DiscordApiClient::createClient($this->client, $webhook);
    }
}
