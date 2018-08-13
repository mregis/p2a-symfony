<?php

namespace App\Repository\Gefra;

use App\Entity\Gefra\TipoEnvioStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TipoEnvioStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method TipoEnvioStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method TipoEnvioStatus[]    findAll()
 * @method TipoEnvioStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TipoEnvioStatusRepository extends ServiceEntityRepository
{
    /**
     * @var ArrayCollection;
     */
    private $tipoEnvioStatus;
    
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TipoEnvioStatus::class);
        $this->tipoEnvioStatus = new ArrayCollection();
    }

    /**
     * @param $codigo string
     * @return TipoEnvioStatus
     */
    public function findOneByName($name)
    {
        if (!$tipoEnvioStatus = $this->tipoEnvioStatus->get($name) ) {
            $tipoEnvioStatus = $this->findOneBy(array('nome' => $name));
            $this->tipoEnvioStatus->set($name, $tipoEnvioStatus);
        }

        return $tipoEnvioStatus;
    }
    
//    /**
//     * @return TipoEnvioStatus[] Returns an array of TipoEnvioStatus objects
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
    public function findOneBySomeField($value): ?TipoEnvioStatus
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
