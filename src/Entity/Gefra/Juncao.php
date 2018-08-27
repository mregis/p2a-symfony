<?php

namespace App\Entity\Gefra;

use App\Util\StringUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Gefra\JuncaoRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"banco", "codigo"})})
 */
class Juncao implements \Serializable
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", options={"comment"="Identificador do registro"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $nome;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $canonical_name;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $codigo;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $cidade;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $canonical_city;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $uf;

    /**
     * @ORM\Column(type="integer")
     */
    private $banco;

    /**
     * @var Collection|Envio[]
     * @ORM\OneToMany(targetEntity="App\Entity\Gefra\Envio", mappedBy="juncao", orphanRemoval=true)
     */
    private $envios;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gefra\SLA", mappedBy="juncao", orphanRemoval=true)
     */
    private $slas;

    public function __construct()
    {
        $this->isActive = true;
        $this->envios = new ArrayCollection();
        $this->slas = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome(string $nome)
    {
        $this->nome = $nome;
        $this->canonical_name = StringUtils::slugify($nome);
        return $this;
    }

    /**
     * @return int
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getCidade()
    {
        return $this->cidade;
    }

    public function setCidade(string $cidade)
    {
        $this->cidade = $cidade;
        $this->canonical_city = StringUtils::slugify($cidade);
        return $this;
    }

    public function getUf()
    {
        return $this->uf;
    }

    public function setUf(string $uf)
    {
        $this->uf = $uf;

        return $this;
    }

    /**
     * @return Banco
     */
    public function getBanco()
    {
        return $this->banco;
    }

    public function setBanco(int $banco)
    {
        $this->banco = $banco;

        return $this;
    }

    public function getIsActive()
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nome' => $this->nome,
            'uf' => $this->uf,
            'cidade' => $this->cidade,
            'banco' => $this->banco,
            'ativo' => $this->isActive,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->codigo,
            $this->nome,
            $this->uf,
            $this->cidade,
            $this->banco,
            $this->isActive,
            ) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('[%04d] %s', $this->codigo, $this->nome);
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
            $envio->setJuncao($this);
        }

        return $this;
    }

    public function removeEnvio(Envio $envio): self
    {
        if ($this->envios->contains($envio)) {
            $this->envios->removeElement($envio);
            // set the owning side to null (unless already changed)
            if ($envio->getJuncao() === $this) {
                $envio->setJuncao(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SLA[]
     */
    public function getSlas(): Collection
    {
        return $this->slas;
    }

    public function addSla(SLA $sla): self
    {
        if (!$this->slas->contains($sla)) {
            $this->slas[] = $sla;
            $sla->setJuncao($this);
        }

        return $this;
    }

    public function removeSla(SLA $sla): self
    {
        if ($this->slas->contains($sla)) {
            $this->slas->removeElement($sla);
            // set the owning side to null (unless already changed)
            if ($sla->getJuncao() === $this) {
                $sla->setJuncao(null);
            }
        }

        return $this;
    }
}
