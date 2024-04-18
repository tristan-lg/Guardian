<?php

namespace App\MessageHandler;

use App\Entity\Project;
use App\Message\RunAnalysis;
use App\Service\ProjectAnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RunAnalysisHandler
{
    public function __construct(
        private readonly ProjectAnalysisService $projectAnalysisService,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function __invoke(RunAnalysis $runAnalysis): void
    {
        if ($project = $this->em->getRepository(Project::class)->find($runAnalysis->getProjectId())) {
            $this->projectAnalysisService->runAnalysis($project);
        }
    }
}
