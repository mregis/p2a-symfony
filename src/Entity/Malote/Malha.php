<?php

namespace App\Entity\Malote;

use App\Util\StringUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Malote\MalhaRepository")
 */
class Malha implements \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", options={"comment"="Identificador do registro na tabela malha"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nome;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $nome_canonico;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ativo;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Malote\Roteiro", mappedBy="malha")
     */
    private $roteiros;

    public function __construct()
    {
        $this->ativo = true;
        $this->roteiros = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;
        $this->nome_canonico = StringUtils::slugify($nome);

        return $this;
    }

    public function getNomeCanonico(): ?string
    {
        return $this->nome_canonico;
    }

    public function setNomeCanonico(string $nome_canonico): self
    {
        $this->nome_canonico = $nome_canonico;

        return $this;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'nome' => $this->nome,
            'nome_canonico', $this->nome_canonico,
            'ativo' => $this->ativo,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->nome,
            $this->nome_canonico,
            $this->ativo,
            ) = unserialize($serialized);
    }

    public function getAtivo(): ?bool
    {
        return $this->ativo;
    }

    public function setAtivo(bool $ativo): self
    {
        $this->ativo = $ativo;

        return $this;
    }

    /**
     * @return Collection|Roteiro[]
     */
    public function getRoteiros(): Collection
    {
        return $this->roteiros;
    }

    public function addRoteiro(Roteiro $roteiro): self
    {
        if (!$this->roteiros->contains($roteiro)) {
            $this->roteiros[] = $roteiro;
            $roteiro->setMalha($this);
        }

        return $this;
    }

    public function removeRoteiro(Roteiro $roteiro): self
    {
        if ($this->roteiros->contains($roteiro)) {
            $this->roteiros->removeElement($roteiro);
            // set the owning side to null (unless already changed)
            if ($roteiro->getMalha() === $this) {
                $roteiro->setMalha(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nome;
    }
}
