<?php

namespace App\MessageHandler;

use App\Entity\Project;
use App\Message\ClearProjectAnalyses;
use App\Service\AnalysisService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ClearProjectAnalysesHandler
{
    public function __construct(
        private readonly AnalysisService $projectAnalysisService,
        private readonly EntityManagerInterface $em
    ) {}

    public function __invoke(ClearProjectAnalyses $clearProjectAnalyses): void
    {
        if ($project = $this->em->getRepository(Project::class)->find($clearProjectAnalyses->getProjectId())) {
            $this->projectAnalysisService->clearOutdatedAnalysis($project);
        }
    }
}
