<?php

namespace App\Command;

use App\Entity\Analysis;
use App\Service\Notification\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:notification:test',
    description: 'Run a project scan',
)]
class TestDiscordCommand extends Command
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test Discord notification');

        // Set the project
        $analysis = $this->em->getRepository(Analysis::class)->findOneBy(['grade' => 'E']);
        if (!$analysis) {
            $io->error('No analysis with E grade found');

            return Command::FAILURE;
        }

        $io->writeln('Send notification...');
        $this->notificationService->sendAnalysisDoneNotification($analysis);

        return Command::SUCCESS;
    }
}
