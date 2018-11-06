<?php

namespace App\Entity\Malote;

use App\Entity\Agencia\Agencia;
use App\Entity\Main\Transportadora;
use App\Entity\Main\Unidade;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Malote\RoteiroRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"agencia"})})
 */
class Roteiro implements \Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", options={"comment"="Identificador do registro na tabela roteiro"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $agencia;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $rota;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $transportadora;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $unidade;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $frequencia;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Malote\Malote", mappedBy="roteiro")
     */
    private $malotes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Malote\Malha", inversedBy="roteiros")
     * @ORM\JoinColumn(nullable=false)
     */
    private $malha;

    /**
     * @ORM\Column(type="datetime")
     */
    private $criado_em;

    /**
     * @ORM\Column(type="integer")
     */
    private $lote;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ativo;
    
    public function __construct()
    {
        $this->malotes = new ArrayCollection();
        $this->criado_em = new \DateTime();
        $this->ativo = true;
        $this->frequencia = 'diario';
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRota(): ?string
    {
        return $this->rota;
    }

    public function setRota(string $rota): self
    {
        $this->rota = $rota;

        return $this;
    }

    public function getFrequencia(): ?string
    {
        return $this->frequencia;
    }

    public function setFrequencia(string $frequencia): self
    {
        $this->frequencia = $frequencia;

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
            'rota' => $this->rota,
            'frequencia' => $this->frequencia,
            'criado_em' => $this->criado_em,
            'ativo' => $this->ativo,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->rota,
            $this->frequencia,
            $this->criado_em,
            $this->ativo,
            ) = unserialize($serialized);
    }

    /**
     * @return Collection|Malote[]
     */
    public function getMalotes(): Collection
    {
        return $this->malotes;
    }

    public function addMalote(Malote $malote): self
    {
        if (!$this->malotes->contains($malote)) {
            $this->malotes[] = $malote;
            $malote->setRoteiro($this);
        }

        return $this;
    }

    public function removeMalote(Malote $malote): self
    {
        if ($this->malotes->contains($malote)) {
            $this->malotes->removeElement($malote);
            // set the owning side to null (unless already changed)
            if ($malote->getRoteiro() === $this) {
                $malote->setRoteiro(null);
            }
        }

        return $this;
    }

    public function getCriadoEm(): ?\DateTimeInterface
    {
        return $this->criado_em;
    }

    public function setCriadoEm(\DateTimeInterface $criado_em): self
    {
        $this->criado_em = $criado_em;

        return $this;
    }

    public function getMalha(): ?Malha
    {
        return $this->malha;
    }

    public function setMalha(?Malha $malha): self
    {
        $this->malha = $malha;

        return $this;
    }

    public function getLote(): ?int
    {
        return $this->lote;
    }

    public function setLote(int $lote): self
    {
        $this->lote = $lote;

        return $this;
    }

    public function getAgencia(): ?string
    {
        return $this->agencia;
    }

    public function setAgencia(string $agencia): self
    {
        $this->agencia = $agencia;

        return $this;
    }

    public function getTransportadora(): ?string
    {
        return $this->transportadora;
    }

    public function setTransportadora(string $transportadora): self
    {
        $this->transportadora = $transportadora;

        return $this;
    }

    public function getUnidade(): ?string
    {
        return $this->unidade;
    }

    public function setUnidade(string $unidade): self
    {
        $this->unidade = $unidade;

        return $this;
    }
}
