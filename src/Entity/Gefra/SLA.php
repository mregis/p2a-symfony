<?php

namespace App\Entity\Gefra;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Gefra\SLARepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"operador_id", "juncao_id"})})
 */
class SLA
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gefra\Operador", inversedBy="slas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $operador;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gefra\Juncao", inversedBy="slas")
     * @ORM\JoinColumn(nullable=false)
     */
    private $juncao;

    /**
     * @ORM\Column(type="integer", options={"default"=3, "comment"="Prazo maximo para cumprimento de entrega em dias"})
     */
    private $prazo;

    public function getId()
    {
        return $this->id;
    }

    public function getOperador(): ?Operador
    {
        return $this->operador;
    }

    public function setOperador(?Operador $operador): self
    {
        $this->operador = $operador;

        return $this;
    }

    public function getJuncao(): ?Juncao
    {
        return $this->juncao;
    }

    public function setJuncao(?Juncao $juncao): self
    {
        $this->juncao = $juncao;

        return $this;
    }

    public function getPrazo(): ?int
    {
        return $this->prazo;
    }

    public function setPrazo(int $prazo): self
    {
        $this->prazo = $prazo;

        return $this;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
{
    return serialize(array(
        'id' => $this->id,
        'operador' => unserialize($this->getOperador()->serialize()),
        'juncao' => unserialize($this->getJuncao()->serialize()),
        'prazo' => $this->prazo,
    ));
}

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
{
    list (
        $this->id,
        $this->operador,
        $this->juncao,
        $this->prazo,
        ) = unserialize($serialized);
}
}
