<?php

namespace App\Repository\Localidade;

use App\Entity\Localidade\UF;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UF|null find($id, $lockMode = null, $lockVersion = null)
 * @method UF|null findOneBy(array $criteria, array $orderBy = null)
 * @method UF[]    findAll()
 * @method UF[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UFRepository extends ServiceEntityRepository
{

    /**
     * @var array
     */
    private $siglasUf = array();

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UF::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('u')
            ->where('u.something = :value')->setParameter('value', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    /**
     * @param $sigla string
     * @return UF
     */
    public function findBySigla($sigla)
    {
        if (isset($this->siglasUf[$sigla])) {
            $uf = $this->siglasUf[$sigla];
        } else {
            $uf = $this->findOneBy(array('sigla' => $sigla));
            $this->siglasUf[$sigla] = $uf;
        }

        return $uf;
    }
}
