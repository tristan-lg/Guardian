<?php

namespace App\Command\Cron;

use App\Entity\Project;
use App\Service\AnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask(schedule: 'guardian', expression: '# 6 * * *')]
#[AsCommand(
    name: 'app:cron:schedule:purge-old-analyses',
    description: 'Schedule purge of old projects analyses',
)]
class PurgeProjectsAnalysesCron
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AnalysisService $projectAnalysisService,
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        foreach ($this->em->getRepository(Project::class)->findAll() as $project) {
            $this->projectAnalysisService->scheduleClearAnalyses($project);
        }

        $io->success('Purge scheduled successfully!');

        return Command::SUCCESS;
    }
}