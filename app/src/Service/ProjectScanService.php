<?php

namespace App\Service;

use App\Entity\Project;
use App\Exception\CredentialsExpiredException;
use App\Exception\ProjectFileNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ProjectScanService
{
    public function __construct(
        private readonly GitlabApiService $clientService,
        private readonly EntityManagerInterface $em
    ) {}

    /**
     * Scan the project and update the file list.
     *
     * @throws CredentialsExpiredException
     */
    public function scanProject(Project $project): void
    {
        if (!$project->getCredential()?->isValid()) {
            throw new CredentialsExpiredException();
        }

        $client = $this->clientService->getClient($project->getCredential());

        $expectedFiles = ['composer.lock', 'composer.json'];

        // Scan files
        $foundFileList = [];
        foreach ($expectedFiles as $file) {
            $found = $client->searchFileOnProject($project, $file);
            $foundFileList[$file] = $found;

            if (!$found) {
                throw new Exception("Le fichier '{$file}' est introuvable");
            }
        }

        $project->setFiles($foundFileList);

        // Scan avatar & gitUrl & name
        $projectInfos = $client->getProjectInfos($project);
        $project->setName(ucfirst($projectInfos['name'] ?? 'Projet inconnu'));
        $project->setAvatarUrl($projectInfos['avatar_url'] ?? null);
        $project->setGitUrl($projectInfos['web_url'] ?? null);
        $project->setNamespace($projectInfos['namespace']['name'] ?? null);

        if (!$project->getAlias()) {
            $project->setAlias($project->getName());
        }

        $this->em->flush();
    }

    public function getFileJsonContent(Project $project, string $fileKey): array
    {
        $filepath = $project->getFiles()[$fileKey];
        if (!$filepath) {
            throw new ProjectFileNotFoundException($fileKey);
        }

        $client = $this->clientService->getClient($project->getCredential());
        $fileContent = $client->getFileContent($project, $filepath);

        return (array) json_decode($fileContent, true);
    }
}
