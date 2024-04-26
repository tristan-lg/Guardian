<?php

namespace App\Scheduler;

use App\Entity\Project;
use App\Service\ProjectAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask(schedule: 'analysis', expression: '# 7 * * *', jitter: 10, method: 'runGlobalAnalysis')]
#[AsCronTask(schedule: 'analysis', expression: '# 7 * * *', jitter: 10, method: 'purgeProjectsAnalyses')]
class AnalysisProvider
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProjectAnalysisService $projectAnalysisService
    ) {}

    public function runGlobalAnalysis(): void
    {
        foreach ($this->em->getRepository(Project::class)->findAll() as $project) {
            $this->projectAnalysisService->scheduleAnalysis($project, true);
        }
    }

    public function purgeProjectsAnalyses(): void
    {
        foreach ($this->em->getRepository(Project::class)->findAll() as $project) {
            $this->projectAnalysisService->scheduleClearAnalyses($project);
        }
    }
}
