<?php

namespace App\Repository\Gefra;

use App\Entity\Gefra\Transportadora;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Juncao|null find($id, $lockMode = null, $lockVersion = null)
 * @method Juncao|null findOneBy(array $criteria, array $orderBy = null)
 * @method Juncao[]    findAll()
 * @method Juncao[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransportadoraRepository extends ServiceEntityRepository
{

    /**
     * @var ArrayCollection;
     */
    private $transportadoras;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Transportadora::class);
        $this->transportadoras = new ArrayCollection();
    }

    /**
     * @param $codigo string
     * @return Transportadora
     */
    public function findOneByCodigo($codigo)
    {
        if (!$transportadora = $this->transportadoras->get($codigo) ) {
            $transportadora = $this->findOneBy(array('codigo' => $codigo));
            $this->transportadoras->set($codigo, $transportadora);
        }

        return $transportadora;
    }
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
