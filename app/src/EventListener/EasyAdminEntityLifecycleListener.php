<?php

namespace App\EventListener;

use App\Entity\Interface\NameableEntityInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

final class EasyAdminEntityLifecycleListener
{
    #[AsEventListener]
    public function onAfterEntityDeletedEvent(AfterEntityDeletedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $entityName = $entity instanceof NameableEntityInterface ? $entity::getSingular() : (string) $entity;

        // @phpstan-ignore-next-line
        noty()->addDeleted($entityName);
    }

    #[AsEventListener]
    public function onAfterEntityUpdatedEvent(AfterEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $entityName = $entity instanceof NameableEntityInterface ? $entity::getSingular() : (string) $entity;

        // @phpstan-ignore-next-line
        noty()->addUpdated($entityName);
    }

    #[AsEventListener]
    public function onAfterEntityPersistedEvent(AfterEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        $entityName = $entity instanceof NameableEntityInterface ? $entity::getSingular() : (string) $entity;

        // @phpstan-ignore-next-line
        noty()->addCreated($entityName);
    }
}
