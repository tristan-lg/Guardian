<?php

namespace App\Service;

use App\Entity\Analysis;
use App\Entity\Package;
use App\Entity\Project;
use App\Message\RunAnalysis;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Messenger\MessageBusInterface;

class ProjectAnalysisService
{
    public function __construct(
        private readonly GitlabApiService $clientService,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
        private readonly ProjectScanService $projectScanService
    ) {}

    public function scheduleAnalysis(Project $project): void
    {
        $this->messageBus->dispatch(new RunAnalysis($project->getId()));
    }

    public function runAnalysis(Project $project): void
    {
        $analysis = new Analysis();
        $analysis->setProject($project);
        $analysis->setRunAt(new DateTimeImmutable());

        // Get composer.json and composer.lock content
        $composerJson = $this->projectScanService->getFileJsonContent($project, 'composer.json');
        $composerLock = $this->projectScanService->getFileJsonContent($project, 'composer.lock');

        // Create list of packages
        $packages = $this->createPackageList($composerJson, $composerLock);
        foreach ($packages as $package) {
            $analysis->addPackage($package);
        }

        dd($packages);


        dd($composerJson);
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

    /**
     * @return array
     */
    private function createPackageList(array $json, array $lock): array
    {
        $packageList = [];
        foreach ($lock['packages'] as $lockPackage) {
            $jsonPackage = $json['require'][$lockPackage['name']] ?? null;

            $package = (new Package())
                ->setName($lockPackage['name'])
                ->setInstalledVersion($lockPackage['version'])
                ->setRequiredVersion($jsonPackage)

            ;
            $packageList[$package['name']] = $package['version'];
        }


    }


}
