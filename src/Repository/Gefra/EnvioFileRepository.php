<?php

namespace App\Repository\Gefra;

use App\Entity\Gefra\EnvioFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EnvioFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method EnvioFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method EnvioFile[]    findAll()
 * @method EnvioFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnvioFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EnvioFile::class);
    }

//    /**
//     * @return EnvioFile[] Returns an array of EnvioFile objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EnvioFile
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
