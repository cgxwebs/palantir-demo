<?php

namespace App\Repository;

use App\Entity\DataDeletionLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DataDeletionLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataDeletionLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataDeletionLog[]    findAll()
 * @method DataDeletionLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DataDeletionLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataDeletionLog::class);
    }

    // /**
    //  * @return DataDeletionLog[] Returns an array of DataDeletionLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DataDeletionLog
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
