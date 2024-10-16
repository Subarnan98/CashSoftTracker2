<?php

namespace App\Repository;

use App\Entity\NotificationUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationUser>
 */
class NotificationUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationUser::class);
    }


    /**
     * Get all columns from notification and user tables for a given user id
     * 
     * @param int $userId
     * @return array
     */
    public function findNotificationsByUserId($userId)
    {
        $queryBuilder = $this->createQueryBuilder('nu')     // 'nu' is the alias for the NotificationUser entity
            ->select('nu')                                  // Selecting all columns from NotificationUser entity
            ->addSelect('n', 'u')                           // Selecting all columns from Notification and User entity
            ->innerJoin('nu.notification', 'n')             // Joining the Notification entity using the alias 'n'
            ->innerJoin('nu.user', 'u')                     // Joining the User entity using the alias 'u'
            ->where('nu.user = :userId')                    // Filtering by user id
            ->andWhere('nu.isRead = false')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
        
        return $queryBuilder;
    }


    //    /**
    //     * @return NotificationUser[] Returns an array of NotificationUser objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('n.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?NotificationUser
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
