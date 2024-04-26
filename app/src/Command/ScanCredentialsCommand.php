<?php

namespace App\Command;

use App\Entity\Credential;
use App\Service\CredentialsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:credentials:scan',
    description: 'Run a credentials scan',
)]
class ScanCredentialsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly CredentialsService $credentialsService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Set the project
        $credentials = $this->em->getRepository(Credential::class)->findAll();

        $io->title(sprintf('Scanning %d credentials', count($credentials)));
        foreach ($credentials as $credential) {
            $this->credentialsService->runCredentialCheck($credential);

            if ($credential->isExpired()) {
                $io->error(sprintf('Credential %s has expired since %s', $credential->getName(), $credential->getExpireAt()?->format('Y-m-d')));
            } elseif ($credential->isExpiredIn(30)) {
                $io->warning(sprintf('Credential %s is about to expire at %s', $credential->getName(), $credential->getExpireAt()?->format('Y-m-d')));
            }
        }

        $io->success('All credentials have been scanned');

        return Command::SUCCESS;
    }
}
