<?php

namespace App\Repository\Gefra;

use App\Entity\Gefra\Envio;
use App\Entity\Gefra\Ocorrencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Ocorrencia|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ocorrencia|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ocorrencia[]    findAll()
 * @method Ocorrencia[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OcorrenciaRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Ocorrencia::class);
    }

//    /**
//     * @return Ocorrencia[] Returns an array of Ocorrencia objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ocorrencia
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @param envio $envio
     * @return mixed
     */
    public function findLastByEnvio(Envio $envio)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.envio = :val')
            ->setParameter('val', $envio)
            ->orderBy('o.created_at', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
