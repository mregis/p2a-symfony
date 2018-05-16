<?php

namespace App\Repository\Localidade;

use App\Entity\Localidade\Regiao;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Regiao|null find($id, $lockMode = null, $lockVersion = null)
 * @method Regiao|null findOneBy(array $criteria, array $orderBy = null)
 * @method Regiao[]    findAll()
 * @method Regiao[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegiaoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Regiao::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('r')
            ->where('r.something = :value')->setParameter('value', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
