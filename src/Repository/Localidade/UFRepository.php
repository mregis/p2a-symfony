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
     * @var ArrayCollection
     */
    private $siglasUf;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UF::class);
        $this->siglasUf = new ArrayCollection();
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
        if (!$uf = $this->siglasUf->get($sigla) ) {
            $uf = $this->findOneBy(array('sigla' => $sigla));
            $this->siglasUf->set($sigla, $uf);
        }

        return $uf;
    }
}
