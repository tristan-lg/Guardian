<?php

namespace App\Component;

use App\Entity\Credential;
use App\Entity\DTO\ProjectApiDTO;
use App\Entity\Project;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class GitlabApiClient
{
    public const string API_VERSION = 'v4';
    private const array EXCLUDED_DIRS = ['vendor', 'node_modules', '.idea', 'docker'];

    private array $headers;

    protected function __construct(
        private readonly HttpClientInterface $client,
        private readonly Credential $credential
    ) {
        $this->headers = ['Authorization' => 'Bearer ' . $credential->getAccessToken()];
    }

    public function checkCredentials(): bool
    {
        try {
            return $this->get('version')->getStatusCode() === Response::HTTP_OK;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return ProjectApiDTO[]
     */
    public function getAssociatedProjects(): array
    {
        $projects = json_decode($this->get('projects', [
            'min_access_level' => 10,
            'simple' => true,
            'per_page' => 100
        ])->getContent(), true);

        return array_map(fn($project) => new ProjectApiDTO($project['id'], $project['name_with_namespace']), $projects);
    }

    private function get(string $endpoint, array $options = []): ResponseInterface
    {
        return $this->client->request(
            'GET',
            'https://' . $this->credential->getDomain() . '/api/' . self::API_VERSION . '/' . $endpoint,
            [
                'headers' => $this->headers,
                'query' => $options,
            ]
        );
    }

    public static function createClient(
        HttpClientInterface $client,
        Credential $credential,
    ): GitlabApiClient {
        return new self($client, $credential);
    }

    public function getFileContent(Project $project, string $path): string
    {
        $response = $this->get('projects/' . $project->getProjectId(). '/repository/files/' . urlencode($path) . '/raw', [
            'ref' => $project->getRef(),
        ]);

        return $response->getContent();
    }

    public function searchFileOnProject(Project $project, string $filename, ?string $path = null): ?string
    {
        $trees = $this->requestProjectTree($project, $path);

        if ($file = array_filter($trees, fn ($tree) => $tree['name'] === $filename)) {
            return array_values($file)[0]['path'] ?? null;
        }

        foreach (array_filter($trees, fn($tree) => $tree['type'] === 'tree' && !in_array($tree['name'], self::EXCLUDED_DIRS)) as $tree) {
            if ($file = $this->searchFileOnProject($project, $filename, $tree['path'])) {
                return $file;
            }
        }

        return null;
    }

    private function requestProjectTree(Project $project, ?string $path = null): array
    {
        $response = $this->get('projects/' . $project->getProjectId(). '/repository/tree', [
            'path' => $path,
            'ref' => $project->getRef(),
            'per_page' => 100,
        ]);

        return json_decode($response->getContent(), true);
    }
}
