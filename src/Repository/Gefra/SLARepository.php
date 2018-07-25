<?php

namespace App\Repository\Gefra;

use App\Entity\Gefra\SLA;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SLA|null find($id, $lockMode = null, $lockVersion = null)
 * @method SLA|null findOneBy(array $criteria, array $orderBy = null)
 * @method SLA[]    findAll()
 * @method SLA[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SLARepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SLA::class);
    }

//    /**
//     * @return SLA[] Returns an array of SLA objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SLA
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
