<?php

namespace App\Service\Api;

use App\Entity\DTO\EndOfLifeCycleDto;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class EndOfLifeApiService
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly LoggerInterface $logger,
    ) {}

    public function getPackageVersionInfo(string $package, string $version): EndOfLifeCycleDto
    {
        try {
            return $this->requestPackageVersionInfo($package, $version);
        } catch (Throwable $t) {
            $this->logger->error('EndOfLifeApiService::getPackageVersionInfo: ' . $t->getMessage());

            return EndOfLifeCycleDto::fromArray([]);
        }
    }

    private function requestPackageVersionInfo(string $package, string $version): EndOfLifeCycleDto
    {
        $response = $this->client->request('GET', "https://endoflife.date/api/{$package}/{$version}.json");

        return EndOfLifeCycleDto::fromArray($response->toArray());
    }
}
