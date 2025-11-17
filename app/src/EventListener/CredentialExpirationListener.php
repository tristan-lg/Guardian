<?php

namespace App\EventListener;

use App\Event\CredentialJustExpiredEvent;
use App\Event\CredentialWillExpireEvent;
use App\Service\Notification\NotificationService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

readonly class CredentialExpirationListener
{
    public function __construct(
        private NotificationService $notificationService,
        private EntityManagerInterface $em
    ) {}

    #[AsEventListener]
    public function onCredentialWillExpireEvent(CredentialWillExpireEvent $event): void
    {
        $credential = $event->getCredential();
        if ($event->getDaysBeforeExpiration() > 30 || $credential->isExpired()) {
            return;
        }

        // Notify only if the last notification was sent more than 7 days ago
        $lastNotification = $credential->getLastNotification();
        if (null !== $lastNotification && $lastNotification->diff(new DateTimeImmutable())->days < 7) {
            return;
        }

        // Send notification for each channel
        $this->notificationService->sendCredentialWillExpireNotification(
            $credential,
        );

        // Persist the changes
        $credential->setLastNotification(new DateTimeImmutable());
        $this->em->flush();
    }

    #[AsEventListener]
    public function onCredentialJustExpiredEvent(CredentialJustExpiredEvent $event): void
    {
        $credential = $event->getCredential();

        // Send notification for each channel
        $this->notificationService->sendCredentialJustExpiredNotification(
            $credential,
        );

        // Persist the changes
        $credential->setLastNotification(new DateTimeImmutable());
        $this->em->flush();
    }
}
