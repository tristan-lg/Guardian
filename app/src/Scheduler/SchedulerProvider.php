<?php

namespace App\Scheduler;

use App\Entity\Credential;
use App\Entity\NotificationChannel;
use App\Entity\Project;
use App\Service\AnalysisService;
use App\Service\CredentialsService;
use App\Service\Notification\NotificationCheckService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask(schedule: 'guardian', expression: '# 7 * * *', jitter: 30, method: 'runGlobalAnalysis')]
#[AsCronTask(schedule: 'guardian', expression: '# 6 * * *', jitter: 30, method: 'purgeProjectsAnalyses')]
#[AsCronTask(schedule: 'guardian', expression: '# 6 * * *', jitter: 30, method: 'runCredentialCheck')]
#[AsCronTask(schedule: 'guardian', expression: '# 6 * * *', jitter: 30, method: 'runNotificationsCheck')]
class SchedulerProvider
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AnalysisService $projectAnalysisService,
        private readonly CredentialsService $credentialsService,
        private readonly NotificationCheckService $notificationTestService,
        private readonly LoggerInterface $logger
    ) {}

    public function runGlobalAnalysis(): void
    {
        foreach ($this->em->getRepository(Project::class)->findAll() as $project) {
            try {
                $this->projectAnalysisService->scheduleAnalysis($project, true);
            } catch (Exception $e) {
                $this->logger->error('Error while scheduling analysis', ['message' => $e->getMessage()]);
            }
        }
    }

    public function purgeProjectsAnalyses(): void
    {
        foreach ($this->em->getRepository(Project::class)->findAll() as $project) {
            $this->projectAnalysisService->scheduleClearAnalyses($project);
        }
    }

    public function runCredentialCheck(): void
    {
        foreach ($this->em->getRepository(Credential::class)->findAll() as $credential) {
            $this->credentialsService->scheduleCredentialsCheck($credential, true);
        }
    }

    public function runNotificationsCheck(): void
    {
        foreach ($this->em->getRepository(NotificationChannel::class)->findAll() as $channel) {
            $this->notificationTestService->performNotificationChannelTest($channel);
        }
    }
}
