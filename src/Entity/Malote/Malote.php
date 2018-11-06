<?php

namespace App\Entity\Malote;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Malote\MaloteRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"roteiro_id", "numero"})})
 */
class Malote implements \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", options={"comment"="Identificador do registro na tabela malote"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Malote\Roteiro", inversedBy="malotes")
     * @ORM\JoinColumn(nullable=false, name="roteiro_id")
     */
    private $roteiro;

    /**
     * @ORM\Column(type="integer")
     */
    private $numero;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ativo;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $tipo;

    public function __construct()
    {
        $this->ativo = true;
        $this->tipo = '(NAO COMPE)';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRoteiro(): ?Roteiro
    {
        return $this->roteiro;
    }

    public function setRoteiro(?Roteiro $roteiro): self
    {
        $this->roteiro = $roteiro;

        return $this;
    }

    public function getNumero(): ?int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): self
    {
        $this->numero = $numero;

        return $this;
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

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'numero' => $this->numero,
            'ativo' => $this->ativo,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->numero,
            $this->ativo,
            ) = unserialize($serialized);
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): self
    {
        $this->tipo = $tipo;

        return $this;
    }

}
