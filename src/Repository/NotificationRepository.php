<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }


    /**
    * @return Notification[] Returns an array of Notification objects
    */
    public function getNotification($mag_id, $user_id): array
    {
        $queryBuilder = $this->createQueryBuilder('n')
            ->where('n.magasin = :mag_id')
            ->andWhere('n.user = :user_id')
            ->andWhere('n.isRead = :is_read')
            ->setParameter('mag_id', $mag_id)
            ->setParameter('user_id', $user_id)
            ->setParameter('is_read', false)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $queryBuilder;
    }
      


    //    /**
    //     * @return Notification[] Returns an array of Notification objects
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

    //    public function findOneBySomeField($value): ?Notification
    //    {
    //        return $this->createQueryBuilder('n')
    //            ->andWhere('n.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
