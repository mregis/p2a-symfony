<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 09/02/2018
 * Time: 11:29
 */

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="usuario")
 * @ORM\Entity(repositoryClass="App\Repository\Main\UserRepository")
 */
class User implements UserInterface, EquatableInterface, \Serializable
{

    /**
     * @var string
     * @ORM\Column(type="guid", options={"comment"="Identificador do registro"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var ArrayCollection|UserApplication[]
     * @ORM\OneToMany(targetEntity="UserApplication", mappedBy="user", cascade={"persist"})
     */
    private $userApplication;

    /**
     * @var string
     * @ORM\Column(name="username", type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="senha", type="string", length=100)
     */
    private $password;

    /**
     * @var string
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="nome", type="string", length=180)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="chave_confirmacao", type="string", length=180, nullable=true)
     */
    private $confirmationToken;

    /**
     * @var datetime
     * @ORM\Column(name="senha_requisitada_em", type="datetime", nullable=true)
     */
    private $passwordRequestedAt;

    /**
     * @var bool
     * @ORM\Column(name="estado", type="boolean")
     */
    private $isActive;

    /**
     * @var datetime
     * @ORM\Column(name="ultimo_login", type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @var Profile
     * @ORM\ManyToOne(targetEntity="Profile")
     * @ORM\JoinColumn(name="perfil_id", referencedColumnName="id", nullable=false)
     */
    private $profile;

    /**
     * @var datetime
     * @ORM\Column(name="criado_em", type="datetime", nullable=false)
     */
    private $created_at;

    public function __construct()
    {
        $this->isActive = true;
        $this->created_at = new \DateTime('now');
        $this->userApplication = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return null
     */
    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return array((string) $this->getProfile()->getName());
    }

    /**
     * @return mixed
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param DateTime $lastLogin
     * @return User
     */
    public function setLastLogin(\DateTime $lastLogin)
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }


    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
            $this->profile,
            $this->email,
            $this->name
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
            $this->profile,
            $this->email,
            $this->name
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    /**
     * @param string $confirmationToken
     * @return User
     */

    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function getPasswordRequestedAt()
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param DateTime $passwordRequestedAt
     * @return User
     */
    public function setPasswordRequestedAt(\DateTime $passwordRequestedAt)
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
        return $this;
    }

    /**
     * @param String $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param DateTime $created_at
     * @return User
     */
    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @param Profile $profile
     * @return User
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;
        return $this;
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @return ArrayCollection|UserApplication[]
     */
    public function getUserApplication()
    {
        return $this->userApplication;
    }

    /**
     * @param mixed $userApplication
     * @return User
     */
    public function setUserApplication(UserApplication $userApplication)
    {
        $this->userApplication = $userApplication;
        return $this;
    }

    /**
     * @param UserInterface $user
     */
    public function isEqualTo(UserInterface $user): Bool
    {
        return (
            $this->password === $user->getPassword() &&
            $this->username === $user->getUsername() &&
            $this->isActive === $user->isIsActive() &&
            $this->email === $user->getEmail() &&
            $this->profile === $user->getProfile()
        );

    }

    /**
     * @return Boolean
     */
    public function isDeleted(): bool
    {
        return false;
    }

    /**
     * @return Boolean
     */
    public function isExpired(): bool
    {
        return false;
    }

}