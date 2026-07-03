<?php

namespace App\Command\Cron;

use App\Entity\Credential;
use App\Service\CredentialsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask(schedule: 'guardian', expression: '# 6 * * *')]
#[AsCommand('app:cron:schedule:credential-check', 'Schedule credential checks for all projects')]
class RunCredentialCheckCron
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CredentialsService $credentialsService,
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        foreach ($this->em->getRepository(Credential::class)->findAll() as $credential) {
            $this->credentialsService->scheduleCredentialsCheck($credential, true);
        }

        $io->success('Credential checks scheduled successfully!');

        return Command::SUCCESS;
    }
}
