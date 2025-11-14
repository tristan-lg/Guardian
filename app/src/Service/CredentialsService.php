<?php

namespace App\Service;

use App\Entity\Credential;
use App\Event\CredentialJustExpiredEvent;
use App\Event\CredentialWillExpireEvent;
use App\Message\RunCredentialCheck;
use App\Service\Api\GitlabApiService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\TransportNamesStamp;

class CredentialsService
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly GitlabApiService $gitlabApiService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityManagerInterface $em
    ) {}

    public function scheduleCredentialsCheck(Credential $credential, bool $async = false): void
    {
        $this->messageBus->dispatch(new RunCredentialCheck($credential->getId()), [
            new TransportNamesStamp([$async ? 'async' : 'sync']),
        ]);
    }

    public function runCredentialCheck(Credential $credential): void
    {
        // Do not check already expired credentials
        //        if ($credential->isExpired()) {
        //            return;
        //        }

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

        if ($credential->isExpired()) {
            $this->eventDispatcher->dispatch(new CredentialJustExpiredEvent($credential));
        } else {
            $this->eventDispatcher->dispatch(new CredentialWillExpireEvent($credential));
        }
    }
}
