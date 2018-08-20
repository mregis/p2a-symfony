<?php

namespace App\Repository\Localidade;

use App\Entity\Localidade\Feriado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Feriado|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feriado|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feriado[]    findAll()
 * @method Feriado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeriadoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Feriado::class);
    }

//    /**
//     * @return Feriado[] Returns an array of Feriado objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Feriado
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
