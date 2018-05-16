<?php

namespace App\Repository\Localidade;

use App\Entity\Localidade\Cidade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Cidade|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cidade|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cidade[]    findAll()
 * @method Cidade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CidadeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Cidade::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('c')
            ->where('c.something = :value')->setParameter('value', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
