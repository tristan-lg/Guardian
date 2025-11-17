<?php

namespace App\Component\Client;

use App\Component\Git\TokenData;
use App\Entity\Credential;
use App\Entity\DTO\ProjectApiDTO;
use App\Entity\Project;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class GitlabApiClient
{
    public const string API_VERSION = 'v4';
    private const array EXCLUDED_DIRS = ['vendor', 'node_modules', '.idea', 'docker', 'tests', 'library', 'lib'];
    private const int MAX_LEVELS = 2;

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
            return Response::HTTP_OK === $this->get('version')->getStatusCode();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return ProjectApiDTO[]
     *
     * @throws ClientException
     */
    public function getAssociatedProjects(): array
    {
        $page = 1;
        $maxPage = 2500;
        $projects = [];

        do {
            $fetchedProjects = $this->fetchProjectsPage($page);
            $projects = array_merge($projects, $fetchedProjects);
            $page++;
        } while($page < $maxPage && count($fetchedProjects) > 0);

        return $projects;
    }

    /**
     * @return ProjectApiDTO[]
     */
    private function fetchProjectsPage(int $page): array
    {
        $response = $this->get('projects', [
            'simple' => true,
            'archived' => false,
            'page' => $page,
            'per_page' => 20,
        ]);

        $projects = json_decode($response->getContent(), true);

        if (empty($projects)) {
            return [];
        }

        return array_map(fn ($project) => new ProjectApiDTO($project['id'], $project['name_with_namespace']), $projects);
    }

    public function getBranches(Project $project): array
    {
        return array_map(
            // @phpstan-ignore-next-line
            fn (array $data) => $data['name'],
            // @phpstan-ignore-next-line
            json_decode($this->get('projects/' . $project->getProjectId() . '/repository/branches', [
                'per_page' => 100,
            ])->getContent(), true)
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
        $response = $this->get('projects/' . $project->getProjectId() . '/repository/files/' . urlencode($path) . '/raw', [
            'ref' => $project->getRef(),
        ]);

        return $response->getContent();
    }

    public function getProjectInfos(Project $project): array
    {
        $response = $this->get('projects/' . $project->getProjectId(), [
            'statistics' => false,
            'simple' => true,
        ]);

        // @phpstan-ignore-next-line
        return json_decode($response->getContent(), true);
    }

    public function getCredentialInfos(): ?TokenData
    {
        $response = $this->get('personal_access_tokens/self');

        try {
            // @phpstan-ignore-next-line
            return TokenData::fromArray(json_decode($response->getContent(), true));
        } catch (Throwable $t) {
            if (Response::HTTP_UNAUTHORIZED === $t->getCode()) {
                return null;
            }

            throw $t;
        }
    }

    public function searchFileOnProject(Project $project, string $filename, ?string $path = null, int $level = 0): ?string
    {
        // Prevent too many recursions
        if ($level >= self::MAX_LEVELS) {
            return null;
        }

        $trees = $this->requestProjectTree($project, $path);

        if ($file = array_filter($trees, fn ($tree) => $tree['name'] === $filename)) {
            return array_values($file)[0]['path'] ?? null;
        }

        foreach (array_filter($trees, fn ($tree) => 'tree' === $tree['type'] && !in_array($tree['name'], self::EXCLUDED_DIRS)) as $tree) {
            if ($file = $this->searchFileOnProject($project, $filename, $tree['path'], $level + 1)) {
                return $file;
            }
        }

        return null;
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

    private function requestProjectTree(Project $project, ?string $path = null): array
    {
        $response = $this->get('projects/' . $project->getProjectId() . '/repository/tree', [
            'path' => $path,
            'ref' => $project->getRef(),
            'per_page' => 100,
        ]);

        return (array) json_decode($response->getContent(), true);
    }
}
