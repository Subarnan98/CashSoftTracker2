<?php

namespace App\Repository;

use App\Entity\UserMagasin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserMagasin>
 */
class UserMagasinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserMagasin::class);
    }


    /**
     * Get all columns from magasin and user tables for a given user id
     * 
     * @param int $userId
     * @return array
     */
    public function findMagasinAndUserByUserId($userId)
    {
        $queryBuilder = $this->createQueryBuilder('um')     // 'um' is the alias for the UserMagasin entity
            ->select('um')                                  // Selecting all columns from UserMagasin entity
            ->addSelect('m', 'u')                           // Selecting all columns from Magasin and User entity
            ->innerJoin('um.magasin', 'm')                  // Joining the Magasin entiy using the alias 'm'
            ->innerJoin('um.user', 'u')                     // Joining the User entity using the alias 'u'
            ->where('um.user = :userId')                    // Filtering by user id
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
        
        return $queryBuilder;
    }


    /**
     * Get all columns from magasin and user tables for a given magasin id
     * 
     * @param int $magasinId
     * @return array
     */
    public function findMagasinAndUserByMagasinId($magasinId)
    {
        $queryBuilder = $this->createQueryBuilder('um')     // 'um' is the alias for the UserMagasin entity
            ->select('um')                                  // Selecting all columns from UserMagasin entity
            ->addSelect('m', 'u')                           // Selecting all columns from Magasin and User entity
            ->innerJoin('um.magasin', 'm')                  // Joining the Magasin entiy using the alias 'm'
            ->innerJoin('um.user', 'u')                     // Joining the User entity using the alias 'u'
            ->where('um.magasin = :magasinId')              // Filtering by magasin id
            ->setParameter('magasinId', $magasinId)
            ->getQuery()
            ->getResult();
        
        return $queryBuilder;
    }



    //    /**
    //     * @return UserMagasin[] Returns an array of UserMagasin objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?UserMagasin
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
