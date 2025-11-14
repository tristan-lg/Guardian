<?php

namespace App\Service\Api\Message;

use App\Component\Client\Message\MattermostApiClient;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MattermostApiService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger
    ) {}

    public function getClient(string $webhook): MattermostApiClient
    {
        return MattermostApiClient::createClient($this->client, $this->logger, $webhook);
    }
}
