<?php

namespace App\Service;

use App\Entity\Advisory;
use App\Entity\Analysis;
use App\Entity\Package;
use App\Entity\Project;
use App\Enum\Severity;
use App\Event\AnalysisDoneEvent;
use App\Message\RunAnalysis;
use Composer\Semver\Semver;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProjectAnalysisService
{
    public const int ANALYSYS_TO_KEEP = 5;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
        private readonly ProjectScanService $projectScanService,
        private readonly PackagistApiService $packagistApiService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function scheduleAnalysis(Project $project): void
    {
        $this->messageBus->dispatch(new RunAnalysis($project->getId()));
    }

    public function runAnalysis(Project $project): Analysis
    {
        $previousAnalysis = $project->getLastAnalysis();

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

        // Run checks on all packages
        foreach ($packages as $package) {
            // Check if package has security advisories
            foreach ($this->getPackageAdvisories($package, $advisoriesDb) as $advisory) {
                $package->addAdvisory($advisory);
                $analysis->addAdvisory($advisory);
            }

            // Check if package is outdated
            // TODO

            // Check malformation
            $package->setVersionMalformated(
                $this->isPackageMalformated($package)
            );
        }

        // Add all packages to the analysis
        foreach ($packages as $package) {
            $analysis->addPackage($package);
        }

        // Compute grade
        $analysis->setGrade(
            $this->getGrade($analysis)
        );

        // Count advisories
        $analysis->setCveCount(
            array_sum(array_map(fn (Package $package) => $package->getAdvisories()->count(), $packages))
        );

        // Compute analysis hash
        $advisoriesIds = array_map(fn (Advisory $advisory) => $advisory->getAdvisoryId(), $analysis->getAdvisoriesOrdered());
        $analysis->setAdvisoryHash(hash('sha256', implode(',', $advisoriesIds)));

        // Add all packages to the analysis, persist & flush
        $analysis->setEndAt(new DateTimeImmutable());
        $this->em->persist($analysis);
        $this->em->flush();

        // Dispatch the event
        $this->eventDispatcher->dispatch(new AnalysisDoneEvent($analysis, $previousAnalysis));

        return $analysis;
    }

    public function clearOutdatedAnalysis(Project $project, int $analysesToKeep = self::ANALYSYS_TO_KEEP): void
    {
        $analyses = $project->getAnalyses();
        if ($analyses->count() <= $analysesToKeep) {
            return;
        }

        $analyses = $analyses->slice(0, $analyses->count() - $analysesToKeep);
        foreach ($analyses as $analysis) {
            $project->removeAnalysis($analysis);
            $this->em->remove($analysis);
        }

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
                ->setSubDependency(null === $jsonPackage)
            ;
            $packageList[] = $package;
        }

        return $packageList;
    }

    private function isPackageMalformated(Package $package): bool
    {
        if (null === $package->getRequiredVersion()) {
            return false;
        }

        return false === Semver::satisfies($package->getInstalledVersion(), $package->getRequiredVersion());
    }

    private function getPackageAdvisories(Package $package, array $advisories): array
    {
        $advisoriesForPackage = $advisories[$package->getName()] ?? null;
        if (null === $advisoriesForPackage) {
            return [];
        }

        return array_map(fn ($adv) => Advisory::fromPackagistApi($adv), array_filter(
            $advisoriesForPackage,
            fn ($adv) => Semver::satisfies($package->getInstalledVersion(), $adv['affectedVersions'])
        ));
    }

    /**
     * Compute the grade for the analysis
     * A (0) => No issue
     * B (1) => Composer.json is malformed, or at least one of these is not the LTS (PHP / Symfony)
     * C (2) => At least one of these is out of security support (end of support) (PHP / Symfony)
     * D (3) => At least one package has CVE (nothing critical)
     * E (4) => At least one package has critical CVE.
     */
    private function getGrade(Analysis $analysis): string
    {
        $grade = 0;
        foreach ($analysis->getPackages() as $package) {
            // Check if package is malformated
            if ($package->isVersionMalformated()) {
                $grade = max($grade, 1);
            }

            // TODO - Check at least one of these is not the LTS (PHP / Symfony) => GRADE B (1)

            // TODO - Check at least one of these is out of security support (end of support) (PHP / Symfony) => GRADE C (2)

            // Check if package has advisories
            if ($package->getAdvisories()->count() > 0) {
                $grade = max($grade, 3);
            }

            // Check if at least one critical severity
            if ($package->getAdvisories()->filter(fn (Advisory $adv) => Severity::CRITICAL === $adv->getSeverityEnum())->count() > 0) {
                $grade = max($grade, 4);
            }
        }

        return ['A', 'B', 'C', 'D', 'E'][$grade];
    }
}
