<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 02/04/2018
 * Time: 19:44
 */

namespace App\Entity\Main;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\Exception;

/**
 * @ORM\Table(name="sistema")
 * @ORM\Entity(repositoryClass="App\Repository\Main\ApplicationRepository")
 */
class Application
{

    /**
     * @var ArrayCollection|UserApplication[]
     * @ORM\OneToMany(targetEntity="UserApplication", mappedBy="application", cascade={"persist"})
     */
    private $userApplication;

    /**
     * @var string
     * @ORM\Column(type="guid", options={"comment"="Identificador do registro"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="sistema_nome", type="string", length=180)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="sistema_codinome", type="string", length=180)
     */
    private $alias;

    /**
     * @var string
     * @ORM\Column(name="uri", type="string", length=255, nullable=true, options={"default"="https://www.addressweb.com.br"})
     */
    private $uri;

    /**
     * @var bool
     * @ORM\Column(name="sistema_estado", type="boolean")
     */
    private $isActive;

    /**
     * @var array
     * @ORM\Column(name="sistema_opcoes", type="json_array", length=255, nullable=true)
     */
    private $options;

    public function __construct() {
        $this->userApplication = new ArrayCollection();
        $this->options = array();
        $this->isActive = true;
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
     * @return Application
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return Application
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return Application
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     * @return Application
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
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
     * @return Application
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
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
            $options->set($option->getName(), $option);
        }
        return $options;
    }

    /**
     * @param ArrayCollection $options
     * @return Application
     */
    public function setOptions(ArrayCollection $options)
    {
        $this->options = [];
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
        $options = $this->getOptions();
        if ($options->contains($option)) {
            return;
        }
        $this->options[]= ($option->serialize());
        return $this;
    }

    /**
     * @param OptionAttribute $option
     * @return $this
     */
    public function removeOption(OptionAttribute $option)
    {
        $options = $this->getOptions();
        if ($options->contains($option) || $options->get($option->getName())) {
            $options->removeElement($option);
            $this->setOptions($options);
        }
        return $this;
    }

    /**
     * @param string $optionName
     * @return OptionAttribute|null
     */
    public function getOption(string $optionName)
    {
        $options = $this->getOptions();
        $option = $options->get($optionName);
        return $option;
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
     * @return Application
     */
    public function setUserApplication(UserApplication $userApplication)
    {
        $this->userApplication = $userApplication;
        return $this;
    }

}