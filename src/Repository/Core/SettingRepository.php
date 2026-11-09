<?php

namespace App\Repository\Core;

use App\Entity\Core\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Setting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Setting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Setting[]    findAll()
 * @method Setting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    /**
     * @param array<mixed> $searchFormData
     */
    public function getPaginatedQuery(array $searchFormData = []): Query
    {
        $qb = $this->createQueryBuilder('t');

        return $qb->getQuery();
    }

    /**
     * Find settings by their property name.
     *
     * @param string $property
     * @return Setting[] Returns an array of Setting objects
     */
    public function findByProperty(string $property): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.property = :property')
            ->setParameter('property', $property)
            ->getQuery()
            ->getResult();
    }
}
