<?php

namespace App\Entity\Localidade;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Localidade\UFRepository")
 */
class UF implements \Serializable
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
     * @var Regiao
     * @ORM\ManyToOne(targetEntity="Regiao", inversedBy="ufs")
     */
    private $regiao;

    /**
     * @var Cidade
     * @ORM\OneToMany(targetEntity="Cidade", mappedBy="uf")
     */
    private $cidades;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $ativo;

    /**
     * @ORM\OneToMany(targetEntity="Feriado", mappedBy="uf", orphanRemoval=true)
     */
    private $feriados;

    public function __construct()
    {
        $this->ativo = true;
        $this->feriados = new ArrayCollection();
        $this->cidades = new ArrayCollection();
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
     * @return UF
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
     * @return UF
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
     * @return UF
     */
    public function setSigla($sigla)
    {
        $this->sigla = $sigla;
        return $this;
    }

    /**
     * @return Regiao
     */
    public function getRegiao()
    {
        return $this->regiao;
    }

    /**
     * @param Regiao
     * @return UF
     */
    public function setRegiao(Regiao $regiao)
    {
        $this->regiao = $regiao;
        return $this;
    }

    /**
     * @return Collection|Cidade[]
     */
    public function getCidades(): Collection
    {
        return $this->cidades;
    }

    /**
     * @param Cidade $cidade
     * @return $this
     */
    public function addCidade(Cidade $cidade): self
    {
        if (!$this->cidades->contains($cidade)) {
            $this->cidades[] = $cidade;
            $cidade->setUF($this);
        }

        return $this;
    }

    /**
     * @param Cidade $cidade
     * @return $this
     */
    public function removeCidade(Cidade $cidade): self
    {
        if ($this->cidades->contains($cidade)) {
            $this->cidades->removeElement($cidade);
            if ($cidade->getUF() === $this) {
                $cidade->setUF(null);
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
    public function setAtivo($ativo): self
    {
        $this->ativo = $ativo;
        return $this;
    }

    public function __toString(): string
    {
        return $this->getSigla();
    }

    /**
     * @return Collection|Feriado[]
     */
    public function getFeriados(): ?Collection
    {
        return $this->feriados;
    }

    public function addFeriado(Feriado $feriado): self
    {
        if (!$this->feriados->contains($feriado)) {
            $this->feriados[] = $feriado;
            $feriado->setTipo($this);
        }

        return $this;
    }

    public function removeFeriado(Feriado $feriado): self
    {
        if ($this->feriados->contains($feriado)) {
            $this->feriados->removeElement($feriado);
            // set the owning side to null (unless already changed)
            if ($feriado->getTipo() === $this) {
                $feriado->setTipo(null);
            }
        }

        return $this;
    }


    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'nome' => $this->nome,
            'sigla' => $this->sigla,
            'ativo' => $this->ativo,
            'regiao' => unserialize($this->getRegiao()->serialize()),
        ));
    }

        /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->nome,
            $this->sigla,
            $this->ativo,
            $this->regiao,
            ) = unserialize($serialized);
    }
}
