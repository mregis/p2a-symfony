<?php

namespace App\Repository\Localidade;

use App\Entity\Localidade\Feriado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Feriado|null find($id, $lockMode = null, $lockVersion = null)
 * @method Feriado|null findOneBy(array $criteria, array $orderBy = null)
 * @method Feriado[]    findAll()
 * @method Feriado[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeriadoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Feriado::class);
    }

//    /**
//     * @return Feriado[] Returns an array of Feriado objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Feriado
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return Feriado[]
     */
    public function findAll()
    {
        return $this->createQueryBuilder('f')
            ->orderBy('ABS(DATE_DIFF(f.dt_feriado, CURRENT_DATE()))', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param \DateTime $date_from
     * @param \DateTime $date_to
     * @param null $cidade
     * @param null $uf
     * @return mixed
     */
    public function findAllByInterval(\DateTime $date_from, \DateTime $date_to, $cidade = null, $uf = null)
    {

        $qb = $this->createQueryBuilder('f')
            ->where('f.dt_feriado BETWEEN :from AND :to AND f.tipo = :tiponacional')
            ->setParameters(
                [
                    'from' => $date_from,
                    'to' => $date_to,
                    'tiponacional' => Feriado::TIPOFERIADO_NACIONAL]
            );

        if ($cidade != null) {
            $qb->orWhere('f.local = :cidade AND f.tipo = :tipomunicial')
                ->setParameter('cidade', $cidade)
                ->setParameter('tipomunicial', Feriado::TIPOFERIADO_MUNICIPAL);

        }

        if ($uf != null) {
            $qb->orWhere('f.uf = :uf AND f.tipo = :tipoestadual')
                ->setParameter('uf', $uf)
                ->setParameter('tipoestadual', Feriado::TIPOFERIADO_ESTADUAL)
            ;
        }

        return $qb->getQuery()
            ->getResult();
    }
}
