<?php

namespace App\Repository\Main;

use App\Entity\Main\Transportadora;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Transportadora|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transportadora|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transportadora[]    findAll()
 * @method Transportadora[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
     * @param $nome string
     * @return Transportadora
     */
    public function findOneByNomeCanonico($nome)
    {
        if (!$transportadora = $this->transportadoras->get($nome) ) {
            $transportadora = $this->findOneBy(array('nome_canonico' => $nome));
            $this->transportadoras->set($nome, $transportadora);
        }

        return $transportadora;
    }
//    /**
//     * @return Transportadora[] Returns an array of Transportadora objects
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
    public function findOneBySomeField($value): ?Transportadora
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
