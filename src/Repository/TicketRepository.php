<?php

namespace App\Repository;

use App\Entity\Magasin;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\AST\Join;
use Doctrine\ORM\Query\Expr\Join as ExprJoin;

/**
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }


    public function findAllVisibleQuery(): Query
    {
        return $this->findVisibleQuery()
            ->getQuery();
    }

    // /**
    //  * @return Ticket[] Returns an array of Ticket objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function findById($id)
    {

        return $this->createQueryBuilder('t')
            ->andWhere('t.user_id = :val')
            ->setParameter('val', $id)
            ->orderBy('t.daterec', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
    





    // paramtre id user pour recuperer tous les tickets des magasins  select * from ticket where EXISTS (SELECT `magasin_id` FROM `user_magasin` WHERE user_id = 1 and ticket.mag_id = user_magasin.magasin_id )
    public function findForUser($idMag)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.mag_id = :val')
            ->setParameter('val', $idMag)
            ->orderBy('t.date_register', 'DESC')
            ->getQuery()
            ->getResult()
            ;


    }





    public function findForAdmin()
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.DateRegister', 'DESC')
            ->getQuery()
            ->getResult()
            ;

    }

    public function findByStatus()
    {
        return $this->createQueryBuilder('t')
        ->addOrderBy('t.Status','ASC')
        ->addOrderBy('t.id','DESC')
        ->getQuery()
        ->getResult();
    }
    
    public function findIdByTreatmentStatus():array
    {
        return $this->createQueryBuilder('t')
        ->andWhere('t.Status = 2')
        ->andWhere('DATE_DIFF(t.DateRegister,CURRENT_DATE()) < 7')
        ->addOrderBy('t.id','DESC')
        ->getQuery()
        ->getResult();
    }

    public function findOneEmailByTreatmentStatus($ticket)
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

   




    private function findVisibleQuery() :QueryBuilder
    {
        return  $this->createQueryBuilder('t')
            ->orderBy('t.daterec', 'DESC');

    }
}
