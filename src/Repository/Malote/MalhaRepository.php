<?php

namespace App\Repository\Malote;

use App\Entity\Malote\Malha;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Malha|null find($id, $lockMode = null, $lockVersion = null)
 * @method Malha|null findOneBy(array $criteria, array $orderBy = null)
 * @method Malha[]    findAll()
 * @method Malha[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MalhaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Malha::class);
    }

//    /**
//     * @return Malha[] Returns an array of Malha objects
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
    public function findOneBySomeField($value): ?Malha
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
