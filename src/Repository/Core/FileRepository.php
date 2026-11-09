<?php

namespace App\Repository\Core;

use App\Entity\Core\File;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method File|null find($id, $lockMode = null, $lockVersion = null)
 * @method File|null findOneBy(array $criteria, array $orderBy = null)
 * @method File[]    findAll()
 * @method File[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends ServiceEntityRepository implements CoreRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    /**
     * @return array|File[]
     */
    public function getAllDetachFiles(\DateTime $date): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.refId IS NULL')
            ->andWhere('f.createdAt < :date')
            ->setParameter('date', $date->format('Y-m-d H:i:s'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<int> $ids
     */
    public function deleteByIds(array $ids): ?int
    {
        return $this->createQueryBuilder('f')
            ->delete(File::class, 'f')
            ->where('f.id IN (' . implode(',', $ids) . ')')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return File[]
     * @param array<int> $ids
     */
    public function getFilesAssoc(array $ids): array
    {
        $filesAssoc = [];
        if ($ids) {
            $files = $this->findBy(['id' => $ids]);
            if ($files) {
                foreach ($files as $file) {
                    $filesAssoc[$file->getId()] = $file;
                }
            }
        }
        return $filesAssoc;
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
