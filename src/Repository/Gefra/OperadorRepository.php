<?php

namespace App\Repository\Gefra;

use App\Entity\Gefra\Operador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Juncao|null find($id, $lockMode = null, $lockVersion = null)
 * @method Juncao|null findOneBy(array $criteria, array $orderBy = null)
 * @method Juncao[]    findAll()
 * @method Juncao[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperadorRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Operador::class);
    }

//    /**
//     * @return Juncao[] Returns an array of Juncao objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Juncao
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
