<?php

namespace App\MessageHandler;

use App\Entity\Credential;
use App\Message\RunCredentialCheck;
use App\Service\CredentialsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RunCredentialCheckHandler
{
    public function __construct(
        private readonly CredentialsService $credentialsService,
        private readonly EntityManagerInterface $em
    ) {}

    public function __invoke(RunCredentialCheck $runCredentialCheck): void
    {
        if ($credential = $this->em->getRepository(Credential::class)->find($runCredentialCheck->getCredentialId())) {
            $this->credentialsService->runCredentialCheck($credential);
        }
    }
}
