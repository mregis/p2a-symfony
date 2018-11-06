<?php

namespace App\Repository\Malote;

use App\Entity\Malote\Roteiro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Roteiro|null find($id, $lockMode = null, $lockVersion = null)
 * @method Roteiro|null findOneBy(array $criteria, array $orderBy = null)
 * @method Roteiro[]    findAll()
 * @method Roteiro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoteiroRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Roteiro::class);
    }

//    /**
//     * @return Roteiro[] Returns an array of Roteiro objects
//     */
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
    public function findOneBySomeField($value): ?Roteiro
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
