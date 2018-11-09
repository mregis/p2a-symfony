<?php

namespace App\Repository\Main;

use App\Entity\Main\Unidade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Unidade|null find($id, $lockMode = null, $lockVersion = null)
 * @method Unidade|null findOneBy(array $criteria, array $orderBy = null)
 * @method Unidade[]    findAll()
 * @method Unidade[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UnidadeRepository extends ServiceEntityRepository
{

    /**
     * @var ArrayCollection;
     */
    private $unidades;
    
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Unidade::class);
        $this->unidades = new ArrayCollection();
    }

    /**
     * @param $nome string
     * @return Unidade
     */
    public function findOneByNomeCanonico($nome)
    {
        if (!$unidade = $this->unidades->get($nome) ) {
            $unidade = $this->findOneBy(array('nome_canonico' => $nome));
            $this->unidades->set($nome, $unidade);
        }

        return $unidade;
    }    
//    /**
//     * @return Unidade[] Returns an array of Unidade objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Unidade
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
