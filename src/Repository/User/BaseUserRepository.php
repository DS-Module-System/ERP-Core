<?php

namespace App\Repository\User;

use App\Entity\User\BaseUser;
use App\Service\Core\CoreUtils;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @method BaseUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method BaseUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method BaseUser[]    findAll()
 * @method BaseUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BaseUserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BaseUser::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof BaseUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * @param array<mixed> $searchFormData
     */
    public function getPaginatedUserQuery(array $searchFormData): Query
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.userGroups', 'ug')->select(["t", "ug"])

        ;
        if ($searchFormData) {
            if (!empty($searchFormData['email'])) {
                $qb->andWhere("t.email LIKE :email")->setParameter('email', "%{$searchFormData['email']}%");
            }
            if (!empty($searchFormData['user_group'])) {
                $qb->andWhere("ug.id = :group")->setParameter('group', $searchFormData['user_group']);
            }
        }


        if(empty($searchFormData[CoreUtils::$SORT_LIST_QUERY_NAME])) {
            foreach ($searchFormData[CoreUtils::$SORT_LIST_QUERY_NAME] as $sortColumn=>$sortValue) {

                $qbSortColumn = '';
                if($sortColumn == 'email') {
                    $qbSortColumn = 't.email';
                } elseif ($sortColumn == 'id') {
                    $qbSortColumn = 't.id';
                }
                if(!empty($qbSortColumn)) {
                    $qb->addOrderBy($qbSortColumn, $sortValue);
                }
            }

        }

        return $qb->getQuery();
    }

} 