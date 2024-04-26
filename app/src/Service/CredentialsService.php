<?php

namespace App\Service;

use App\Entity\Credential;
use App\Message\RunCredentialCheck;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class CredentialsService
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly GitlabApiService $gitlabApiService,
        private EntityManagerInterface $em
    ) {}

    public function scheduleCredentialsCheck(Credential $credential, bool $async = false): void
    {
        $this->messageBus->dispatch(new RunCredentialCheck($credential->getId()), [
            new TransportNamesStamp([$async ? 'async' : 'sync']),
        ]);
    }

    public function runCredentialCheck(Credential $credential): void
    {
        $client = $this->gitlabApiService->getClient($credential);
        $infos = $client->getCredentialInfos();

        if (!$infos) {
            $credential->setExpireAt(new DateTimeImmutable('- 1 day'));
        } else {
            $credential->setExpireAt($infos->getExpiresAt()
                ? new DateTimeImmutable($infos->getExpiresAt()->format('Y-m-d'))
                : null
            );
        }

        $this->em->flush();
    }
}
