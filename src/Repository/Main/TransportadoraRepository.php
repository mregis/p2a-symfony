<?php

namespace App\Repository\Main;

use App\Entity\Main\Transportadora;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Transportadora|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transportadora|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transportadora[]    findAll()
 * @method Transportadora[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransportadoraRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Transportadora::class);
    }

//    /**
//     * @return Transportadora[] Returns an array of Transportadora objects
//     */
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
    public function findOneBySomeField($value): ?Transportadora
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
