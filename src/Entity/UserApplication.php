<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 03/04/2018
 * Time: 16:16
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="usuario_sistema")
 * @ORM\Entity(repositoryClass="App\Repository\UserApplicationRepository")
 */
class UserApplication
{

    /**
     * @var string
     * @ORM\Column(type="guid", columnDefinition="DEFAULT gen_random_uuid()", options={"comment"="Identificador do registro"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var array
     * @ORM\Column(name="opcoes", type="json_array", length=255, nullable=true)
     */
    private $options;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userApplication")
     * @ORM\JoinColumn(name="usuario_uuid", referencedColumnName="id", nullable=false)
     */
    private $user;

    /**
     * @var Application
     * @ORM\ManyToOne(targetEntity="Application", inversedBy="userApplication")
     * @ORM\JoinColumn(name="sistema_uuid", referencedColumnName="id", nullable=false)
     */
    private $application;

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return UserApplication
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserApplication
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     * @return UserApplication
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
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
     * @return UserApplication
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }



}