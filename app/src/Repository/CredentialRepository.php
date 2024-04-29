<?php

namespace App\Repository;

use App\Entity\Credential;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Credential>
 *
 * @method null|Credential find($id, $lockMode = null, $lockVersion = null)
 * @method null|Credential findOneBy(array $criteria, array $orderBy = null)
 * @method Credential[]    findAll()
 * @method Credential[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CredentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Credential::class);
    }

    /**
     * @return Credential[]
     */
    public function findExpired(): array
    {
        return array_filter($this->findAll(), fn (Credential $credential) => $credential->isExpired());
    }
}
