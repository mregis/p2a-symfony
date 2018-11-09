<?php

namespace App\Repository\Main;

use App\Entity\Main\UploadDataFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UploadDataFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method UploadDataFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method UploadDataFile[]    findAll()
 * @method UploadDataFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UploadDataFileRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UploadDataFile::class);
    }

//    /**
//     * @return UploadDataFile[] Returns an array of UploadDataFile objects
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
    public function findOneBySomeField($value): ?UploadDataFile
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
