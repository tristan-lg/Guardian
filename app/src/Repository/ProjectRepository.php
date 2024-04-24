<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method null|Project find($id, $lockMode = null, $lockVersion = null)
 * @method null|Project findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function getProjectOrderedByGrade(): array
    {
        /** @var Project[] $projects */
        $projects = $this->createQueryBuilder('p')
            ->getQuery()
            ->getResult()
        ;

        usort($projects, fn (Project $a, Project $b) => $b->getLastGrade() <=> $a->getLastGrade());

        return $projects;
    }
}
