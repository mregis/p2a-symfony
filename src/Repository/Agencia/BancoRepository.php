<?php

namespace App\Repository\Agencia;

use App\Entity\Agencia\Banco;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Banco|null find($id, $lockMode = null, $lockVersion = null)
 * @method Banco|null findOneBy(array $criteria, array $orderBy = null)
 * @method Banco[]    findAll()
 * @method Banco[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BancoRepository extends ServiceEntityRepository
{
    /**
     * @var array;
     */
    private $bancos = array();

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Banco::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('b')
            ->where('b.something = :value')->setParameter('value', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    /**
     * @param $codigo string
     * @return Banco
     */
    public function findOneByCodigo($codigo)
    {
        if (isset($this->bancos[$codigo])) {
            $banco = $this->bancos[$codigo];
        } else {
            $banco = $this->findOneBy(array('codigo' => $codigo));
            $this->bancos[$codigo] = $banco;
        }

        return $banco;
    }
}
