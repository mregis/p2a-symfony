<?php

namespace App\Entity\Localidade;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Localidade\RegiaoRepository")
 */
class Regiao
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $nome;

    /**
     * @var string
     * @ORM\Column(type="string", length=2)
     */
    private $sigla;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="UF", mappedBy="regiao")
     */
    private $ufs;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $ativo;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        $this->ativo = true;
    }
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Regiao
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     * @return Regiao
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return string
     */
    public function getSigla()
    {
        return $this->sigla;
    }

    /**
     * @param string $sigla
     * @return Regiao
     */
    public function setSigla($sigla)
    {
        $this->sigla = $sigla;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUfs()
    {
        return $this->ufs;
    }

    /**
     * @param UF $uf
     * @return $this
     */
    public function addUF(UF $uf)
    {
        if (!$this->ufs->contains($uf)) {
            $this->ufs[] = $uf;
            $uf->setUF($this);
        }

        return $this;
    }

    /**
     * @param UF $uf
     * @return $this
     */
    public function removeUF(UF $uf)
    {
        if ($this->ufs->contains($uf)) {
            $this->ufs->removeElement($uf);
            if ($uf->getRegiao() === $this) {
                $uf->setRegiao(null);
            }
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAtivo()
    {
        return $this->ativo;
    }

    /**
     * @param boolean $ativo
     * @return UF
     */
    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;
        return $this;
    }
}
