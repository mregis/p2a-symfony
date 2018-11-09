<?php

namespace App\Repository\Malote;

use App\Entity\Malote\Malha;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Malha|null find($id, $lockMode = null, $lockVersion = null)
 * @method Malha|null findOneBy(array $criteria, array $orderBy = null)
 * @method Malha[]    findAll()
 * @method Malha[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MalhaRepository extends ServiceEntityRepository
{

    /**
     * @var ArrayCollection;
     */
    private $malhas;

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Malha::class);
        $this->malhas = new ArrayCollection();
    }

    /**
     * @param $nome string
     * @return Malha
     */
    public function findOneByNomeCanonico($nome)
    {
        if (!$malha = $this->malhas->get($nome) ) {
            $malha = $this->findOneBy(array('nome_canonico' => $nome));
            $this->malhas->set($nome, $malha);
        }

        return $malha;
    }
//    /**
//     * @return Malha[] Returns an array of Malha objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Malha
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
