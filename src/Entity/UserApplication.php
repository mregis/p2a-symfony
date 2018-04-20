<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 03/04/2018
 * Time: 16:16
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var ArrayCollection|OptionAttribute[]
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


    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getOptions()
    {
        $options = new ArrayCollection();
        foreach($this->options as $k => $value) {
            /* @var $option OptionAttribute */
            $option = new OptionAttribute();
            $option->unserialize($value);
            $options->add($option);
        }
        return $options;
    }

    /**
     * @param ArrayCollection $options
     * @return Application
     */
    public function setOptions(ArrayCollection $options)
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
        return $this;
    }

    /**
     * @param OptionAttribute $option
     * @return $this
     */
    public function addOption(OptionAttribute $option)
    {
        $this->options[] = $option->serialize();
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