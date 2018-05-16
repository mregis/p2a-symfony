<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 17:34
 */

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="perfil")
 * @ORM\Entity
 */
class Profile
{

    /**
     * @var string
     * @ORM\Column(type="guid", options={"comment"="Identificador do Registro"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=false, unique=true, name="nivel", options={"comment"="Valor potencia de 2"})
     */
    private $level;

    /**
     * @var string
     * @ORM\Column(name="nome", type="string", unique=true, length=180)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="descricao", type="string", length=255)
     */
    private $description;

    /**
     * @var bool
     * @ORM\Column(name="estado", type="boolean")
     */
    private $isActive;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Profile
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     * @return Profile
     */
    public function setLevel($level)
    {
        $this->level = $level;
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
     * @return Profile
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return Profile
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Profile
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

}