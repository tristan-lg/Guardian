<?php

namespace App\Service;

use App\Entity\Project;
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

        $foundFileList = [];
        foreach ($expectedFiles as $file) {
            $found = $client->searchFileOnProject($project, $file);
            $foundFileList[$file] = $found;

            if (!$found) {
                throw new Exception("File '{$file}' not found");
            }
        }

        $project->setFiles($foundFileList);
        $this->em->flush();
    }

    public function getFileJsonContent(Project $project, string $filepath): array
    {
        $client = $this->clientService->getClient($project->getCredential());
        $fileContent = $client->getFileContent($project, $filepath);

        return (array) json_decode($fileContent, true);
    }
}
