<?php

namespace App\Repository\Agencia;

use App\Entity\Agencia\Agencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Agencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agencia[]    findAll()
 * @method Agencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgenciaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Agencia::class);
    }

//    /**
//     * @return Agencia[] Returns an array of Agencia objects
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
    public function findOneBySomeField($value): ?Agencia
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
