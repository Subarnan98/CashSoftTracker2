<?php

namespace App\Repository;

use App\Entity\Magasin;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Magasin|null find($id, $lockMode = null, $lockVersion = null)
 * @method Magasin|null findOneBy(array $criteria, array $orderBy = null)
 * @method Magasin[]    findAll()
 * @method Magasin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MagasinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Magasin::class);
    }

    // /**
    //  * @return Magasin[] Returns an array of Magasin objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Magasin
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function findAllMag()
    {
        return $this->createQueryBuilder('m')
        ->orderBy('m.Nom','ASC')
        ->getQuery()
        ->getResult();
    }

    public function findOneByTreatmentStatus(Ticket $ticket)
    {
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT t.id, m.Email  
            FROM App\Entity\Ticket t 
            INNER JOIN App\Entity\Magasin m
            WHERE t.id = :id 
            ORDER BY t.id DESC '
        )->setParameter('id',$ticket);

        return $query->getResult();
    }
}
