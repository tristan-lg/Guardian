<?php

namespace App\Component;

use App\Entity\Credential;
use App\Entity\Project;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class GitlabApiClient
{
    public const string API_VERSION = 'v4';

    private array $headers;

    private function __construct(
        private readonly HttpClientInterface $client,
        private readonly Credential $credential
    ) {
        $this->headers = ['Authorization' => 'Bearer ' . $credential->getAccessToken()];
    }

    public function check(): bool
    {
        try {
            return $this->get('version')->getStatusCode() === Response::HTTP_OK;
        } catch (Throwable) {
            return false;
        }
    }

    private function get(string $endpoint, array $options = []): ResponseInterface
    {
        return $this->client->request(
            'GET',
            'https://' . $this->credential->getDomain() . '/api/' . self::API_VERSION . '/' . $endpoint,
            [
                'headers' => $this->getHeaders($this->credential),
                'query' => $options,
            ]
        );
    }


    public static function createClient(
        HttpClientInterface $client,
        Credential $credential
    ): GitlabApiClient {
        return new self($client, $credential);
    }

    private function downloadFile(Project $project, string $path): string
    {
        $response = $this->client->request(
            'GET',
            'https://' . $project->getCredential()->getDomain() . '/api/v4/projects/' . $project->getProjectId(). '/repository/files/' . urlencode($path) . '/raw',
            [
                'headers' => $this->getHeaders($project->getCredential()),
                'query' => [
                    'ref' => 'master',
                ],
            ]
        );

        return $response->getContent();
    }

    private function searchComposerLock(Project $project, ?string $path = null): ?array
    {
        $trees = $this->requestTree($project, $path);
        $composerFile = array_filter($trees, fn ($tree) => $tree['name'] === 'composer.json');

        if ($composerFile) {
            return array_values($composerFile)[0];
        }

        foreach (array_filter($trees, fn($tree) => $tree['type'] === 'tree') as $tree) {
            if ($composerFile = $this->searchComposerLock($project, $tree['path'])) {
                return $composerFile;
            }
        }

        return null;
    }

    private function requestTree(Project $project, ?string $path = null): array
    {
        $response = $this->client->request(
            'GET',
            'https://' . $project->getCredential()->getDomain() . '/api/v4/projects/' . $project->getProjectId(). '/repository/tree',
            [
                'headers' => $this->getHeaders( $project->getCredential()),
                'query' => [
                    'path' => $path,
                    'ref' => 'master',
                    'per_page' => 100,
                ],
            ]
        );

        return json_decode($response->getContent(), true);
    }

    private function getHeaders(Credential $credential): array
    {
        return $this->headers;
    }
}
