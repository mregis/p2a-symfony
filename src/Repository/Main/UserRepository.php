<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 09/02/2018
 * Time: 11:41
 */

namespace App\Repository\Main;


use App\Entity\Main\User;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username)
    {
        return $this->createQueryBuilder('u')
            ->where('u.canonical_username = :username')
            ->orWhere('u.canonical_email = :username')
            ->innerJoin('u.profile', 'p')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function loadUsersByUser(User $user)
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.profile', 'p')
            ->andWhere('p.level >= :profile')
            ->setParameter('profile', $user->getProfile()->getLevel())
            ->getQuery()
            ->getResult();
    }

}