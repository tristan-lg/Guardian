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
    ) {
    }

    /**
     * Scan the project and update the file list
     * @param  Project  $project
     *
     * @return void
     */
    public function scanProject(Project $project): void
    {
        $client = $this->clientService->getClient($project->getCredential());

        $expectedFiles = ['composer.lock', 'composer.json'];

        $foundFiles = [];
        foreach ($expectedFiles as $file) {
            $foundFiles[$file] = $client->searchFileOnProject($project, $file);
            if (!$file) {
                throw new Exception("File '{$file}' not found");
            }
        }

        $project->setFiles($foundFiles);
        $this->em->flush();
    }

    public function getFileJsonContent(Project $project, string $filepath): array
    {
        $client = $this->clientService->getClient($project->getCredential());
        $fileContent = $client->getFileContent($project, $filepath);

        return json_decode($fileContent, true);
    }
}
