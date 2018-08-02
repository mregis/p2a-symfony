<?php

namespace App\Repository\Gefra;

use App\Entity\Gefra\Operador;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Juncao|null find($id, $lockMode = null, $lockVersion = null)
 * @method Juncao|null findOneBy(array $criteria, array $orderBy = null)
 * @method Juncao[]    findAll()
 * @method Juncao[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperadorRepository extends ServiceEntityRepository
{

    /**
     * @var ArrayCollection;
     */
    private $operadores;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Operador::class);
        $this->operadores = new ArrayCollection();
    }

    /**
     * @param $codigo string
     * @return Operador
     */
    public function findOneByCodigo($codigo)
    {
        if (!$operador = $this->operadores->get($codigo) ) {
            $operador = $this->findOneBy(array('codigo' => $codigo));
            $this->operadores->set($codigo, $operador);
        }

        return $operador;
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
