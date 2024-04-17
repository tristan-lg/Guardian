<?php

namespace App\Service;

use App\Component\GitlabApiClient;
use App\Entity\Credential;
use App\Entity\Project;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitlabApiService
{
    public function __construct(
        private HttpClientInterface $client,
    ) {
    }

    public function getClient(Credential $credential): GitlabApiClient
    {
        return GitlabApiClient::createClient($this->client, $credential);
    }
}
