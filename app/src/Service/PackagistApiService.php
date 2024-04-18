<?php

namespace App\Service;

use App\Component\GitlabApiClient;
use App\Entity\Credential;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PackagistApiService
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {}

    public function getPackageSecurityAdvisories(array $packages): array
    {
        $response = $this->client->request('GET', 'https://packagist.org/api/security-advisories/', [
            'query' => [
                'packages' => $packages
            ]
        ]);

        return $response->toArray()['advisories'];
    }

}
