<?php

namespace App\Repository;

use App\Entity\Advisory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advisory>
 *
 * @method null|Advisory find($id, $lockMode = null, $lockVersion = null)
 * @method null|Advisory findOneBy(array $criteria, array $orderBy = null)
 * @method Advisory[]    findAll()
 * @method Advisory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvisoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advisory::class);
    }

    //    /**
    //     * @return Advisory[] Returns an array of Advisory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Advisory
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
