<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 20/02/2018
 * Time: 11:23
 */

namespace App\Provider;


use App\Entity\Main\User;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function loadUserByUsername($username)
    {
        $qb = $this->conn->createQueryBuilder();
        $qb->select('*')
            ->from('usuario', 'u')
            ->where('username = ?')
            ->setParameter(0, $username);
        $stmt = $qb->execute();

        if (!$u = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        $user = new User($u['username'], $u['password'], explode(',', $u['roles']), $u['isenabled'], true, true, true);
        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === 'Symfony\Component\Security\Core\User\User';
    }

    /**
     * Generate an Autentication Token to be sent by email and used to reset password
     * With minimun 5 chars
     *
     * @param $username
     * @param int $tokenlength
     * @return string
     */
    public function generateConfirmationToken($username, $tokenlength = 10) {
        $domain = array_merge(range('A', 'Z'), range(0,9));
        //
        if ($tokenlength < 5) {
            throw new InvalidParameterException(sprintf('Token length cannot be %d length', $tokenlength));
        }
        $code = '';
        while (strlen($code) < $tokenlength ) {
            $rnd = rand(0, count($domain)-1);
            $code .= $domain[$rnd];
        }
        $this->conn->createQueryBuilder()
            ->update('usuario')
            ->set('confirmationtoken', '?')
            ->where('username = ?')
            ->setParameter(0, $code)
            ->setParameter(1, $username)
            ->execute();

        return $code;
    }
}