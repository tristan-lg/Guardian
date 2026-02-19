<?php

namespace App\Command\Cron;

use App\Entity\NotificationChannel;
use App\Service\Notification\NotificationCheckService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask(schedule: 'guardian', expression: '# 6 * * *')]
#[AsCommand('app:cron:schedule:notification-check', 'Schedule notification checks for all projects')]
class RunNotificationCheckCron
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificationCheckService $notificationTestService,
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        foreach ($this->em->getRepository(NotificationChannel::class)->findBy(['active' => true]) as $channel) {
            $this->notificationTestService->performNotificationChannelTest($channel);
        }

        $io->success('Notification checks scheduled successfully!');

        return Command::SUCCESS;
    }
}