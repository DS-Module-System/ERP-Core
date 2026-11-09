<?php

namespace App\Repository\Core;

use App\Entity\Core\FileAssociation;
use App\Repository\Core\CoreRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileAssociation>
 *
 * @method FileAssociation|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileAssociation|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileAssociation[]    findAll()
 * @method FileAssociation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileAssociationRepository extends ServiceEntityRepository implements CoreRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileAssociation::class);
    }

    public function save(FileAssociation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FileAssociation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param array<mixed> $searchFormData
     */
    #[\Override]
    public function getPaginatedQuery(array $searchFormData = []): Query
    {
        $qb = $this->createQueryBuilder('t');

        return $qb->getQuery();
    }
}
