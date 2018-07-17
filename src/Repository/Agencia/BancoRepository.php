<?php

namespace App\Repository\Agencia;

use App\Entity\Agencia\Banco;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @var ArrayCollection;
     */
    private $bancos;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Banco::class);
        $this->bancos = new ArrayCollection();
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
        if (!$banco = $this->bancos->get($codigo) ) {
            $banco = $this->findOneBy(array('codigo' => $codigo));
            $this->bancos->set($codigo, $banco);
        }

        return $banco;
    }
}
