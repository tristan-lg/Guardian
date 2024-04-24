<?php

namespace App\Service;

use App\Entity\Project;
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
     */
    public function scanProject(Project $project): void
    {
        $client = $this->clientService->getClient($project->getCredential());

        $expectedFiles = ['composer.lock', 'composer.json'];

        //Scan files
        $foundFileList = [];
        foreach ($expectedFiles as $file) {
            $found = $client->searchFileOnProject($project, $file);
            $foundFileList[$file] = $found;

            if (!$found) {
                throw new Exception("Le fichier '{$file}' est introuvable");
            }
        }

        $project->setFiles($foundFileList);

        //Scan avatar & gitUrl
        $project->setAvatarUrl(
            $client->getProjectAvatar($project)
        );
        $project->setGitUrl(
            $client->getProjectGitUrl($project)
        );

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
