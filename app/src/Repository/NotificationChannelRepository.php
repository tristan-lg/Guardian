<?php

namespace App\Repository;

use App\Entity\NotificationChannel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationChannel>
 *
 * @method null|NotificationChannel find($id, $lockMode = null, $lockVersion = null)
 * @method null|NotificationChannel findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationChannel[]    findAll()
 * @method NotificationChannel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationChannel::class);
    }

    /**
     * @return NotificationChannel[]
     */
    public function findExpired(): array
    {
        return array_filter($this->findAll(), fn (NotificationChannel $channel) => !$channel->isWorking());
    }
}
