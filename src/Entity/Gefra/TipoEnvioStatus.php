<?php

namespace App\Entity\Gefra;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Gefra\TipoEnvioStatusRepository")
 * @ORM\Table(name="tipo_status")
 */
class TipoEnvioStatus implements \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $nome;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $descricao;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gefra\Envio", mappedBy="status")
     */
    private $envios;

    public function __construct()
    {
        $this->envios = new ArrayCollection();
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->nome,
            $this->descricao,
            ) = unserialize($serialized);
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
        $this->nome = strtoupper($nome);

        return $this;
    }

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    /**
     * @return Collection|Envio[]
     */
    public function getEnvios(): Collection
    {
        return $this->envios;
    }

    public function addEnvio(Envio $envio): self
    {
        if (!$this->envios->contains($envio)) {
            $this->envios[] = $envio;
            $envio->setStatus($this);
        }

        return $this;
    }

    public function removeEnvio(Envio $envio): self
    {
        if ($this->envios->contains($envio)) {
            $this->envios->removeElement($envio);
            // set the owning side to null (unless already changed)
            if ($envio->getStatus() === $this) {
                $envio->setStatus(null);
            }
        }

        return $this;
    }

}
