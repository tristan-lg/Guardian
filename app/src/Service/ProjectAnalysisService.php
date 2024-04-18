<?php

namespace App\Service;

use App\Entity\Advisory;
use App\Entity\Analysis;
use App\Entity\Package;
use App\Entity\Project;
use App\Message\RunAnalysis;
use Composer\Semver\Semver;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProjectAnalysisService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
        private readonly ProjectScanService $projectScanService,
        private readonly PackagistApiService $packagistApiService
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

        // Create list of packages & advisories
        $packages = $this->createPackageList($composerJson, $composerLock);
        $packagesNames = array_map(fn (Package $package) => $package->getName(), $packages);
        $advisoriesDb = $this->packagistApiService->getPackageSecurityAdvisories($packagesNames);

        //Run checks on all packages
        foreach ($packages as $package) {
            // Check if package has security advisories
            foreach ($this->getPackageAdvisories($package, $advisoriesDb) as $advisory) {
                $package->addAdvisory($advisory);
            }

            //Check if package is outdated
            //TODO

            //Check malformation
            $package->setVersionMalformated(
                $this->isPackageMalformated($package)
            );
        }

        //Add all packages to the analysis, persist & flush
        $analysis->setEndAt(new DateTimeImmutable());
        foreach ($packages as $package) {
            $analysis->addPackage($package);
        }
        $this->em->persist($analysis);
        $this->em->flush();
    }

    /**
     * @return Package[]
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
                ->setSubDependency($jsonPackage === null)
            ;
            $packageList[] = $package;
        }

        return $packageList;
    }

    private function isPackageMalformated(Package $package): bool
    {
        if ($package->getRequiredVersion() === null) {
            return false;
        }

        return Semver::satisfies($package->getInstalledVersion(), $package->getRequiredVersion()) === false;
    }

    private function getPackageAdvisories(Package $package, array $advisories): array
    {
        $advisoriesForPackage = $advisories[$package->getName()] ?? null;
        if ($advisoriesForPackage === null) {
            return [];
        }

        return array_map(fn ($adv) => Advisory::fromPackagistApi($adv), array_filter(
            $advisoriesForPackage,
            fn ($adv) => Semver::satisfies($package->getInstalledVersion(), $adv['affectedVersions'])
        ));
    }
}
