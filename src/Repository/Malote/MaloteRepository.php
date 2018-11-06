<?php

namespace App\Repository\Malote;

use App\Entity\Malote\Malote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Malote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Malote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Malote[]    findAll()
 * @method Malote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MaloteRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Malote::class);
    }

//    /**
//     * @return Malote[] Returns an array of Malote objects
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
    public function findOneBySomeField($value): ?Malote
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
