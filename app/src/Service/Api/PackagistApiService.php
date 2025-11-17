<?php

namespace App\Service\Api;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PackagistApiService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        #[Autowire('%packagist.user_agent%')] private readonly string $userAgent,
    ) {}

    public function getPackageSecurityAdvisories(array $packages): array
    {
        $advisories = [];

        // Packagist API has a limit if too many packages, so we reduce the number of packages per request
        foreach (array_chunk($packages, 40) as $chunk) {
            $advisories = array_merge($advisories, $this->requestPackageSecurityAdvisories($chunk));
        }

        return $advisories;
    }

    private function requestPackageSecurityAdvisories(array $packages): array
    {
        $response = $this->client->request('GET', 'https://packagist.org/api/security-advisories/', [
            'query' => [
                'packages' => $packages,
            ],
            'headers' => [
                'User-Agent' => $this->userAgent,
            ],
        ]);

        return $response->toArray()['advisories'];
    }
}
