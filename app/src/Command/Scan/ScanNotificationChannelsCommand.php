<?php

namespace App\Command\Scan;

use App\Entity\NotificationChannel;
use App\Service\Notification\NotificationCheckService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:channels:scan',
    description: 'Run a notification channel scan',
)]
class ScanNotificationChannelsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificationCheckService $notificationTestService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Set the project
        $channels = $this->em->getRepository(NotificationChannel::class)->findAll();

        $io->title(sprintf('Scanning %d notification channels', count($channels)));
        foreach ($channels as $channel) {
            $this->notificationTestService->performNotificationChannelTest($channel);

            if ($channel->isWorking()) {
                $io->success(sprintf('Notification channel %s is working', $channel->getName()));
            } else {
                $io->error(sprintf('Notification channel %s is not working', $channel->getName()));
            }
        }

        $io->success('All channels have been scanned');

        return Command::SUCCESS;
    }
}
