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

#[AsCronTask(schedule: 'guardian', expression: '# 7 * * *')]
#[AsCommand('app:cron:schedule:global-analysis', description: 'Schedule global analysis for all projects')]
class RunGlobalAnalysisCron
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AnalysisService $projectAnalysisService,
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        foreach ($this->em->getRepository(Project::class)->findAll() as $project) {
            try {
                $this->projectAnalysisService->scheduleAnalysis($project, true);
                $io->info(sprintf('Scheduled analysis for project %s', $project->getName()));
            } catch (Exception $e) {
                $io->error(sprintf('Error while scheduling analysis for project %s: %s', $project->getName(), $e->getMessage()));
            }
        }

        return Command::SUCCESS;
    }
}
