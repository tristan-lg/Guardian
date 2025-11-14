<?php

namespace App\Service\Api;

use App\Component\Client\GitlabApiClient;
use App\Entity\Credential;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitlabApiService
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {}

    public function getClient(?Credential $credential): GitlabApiClient
    {
        if (!$credential) {
            throw new InvalidArgumentException('Credential is required');
        }

        return GitlabApiClient::createClient($this->client, $credential);
    }
}
