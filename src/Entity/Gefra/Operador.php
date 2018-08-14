<?php

namespace App\Entity\Gefra;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Gefra\OperadorRepository")
 */
class Operador implements \Serializable
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", options={"comment"="Identificador do registro"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $nome;

    /**
     * @var string
     * @ORM\Column(type="string", length=10)
     */
    private $codigo;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $cidade;

    /**
     * @var string
     * @ORM\Column(type="string", length=2)
     */
    private $uf;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $bairro;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $endereco;

    /**
     * @var string
     * @ORM\Column(type="string", length=9)
     */
    private $cep;

    /**
     * @var string
     * @ORM\Column(type="string", length=15)
     */
    private $cnpj;

    /**
     * @var Collection|Envio[]
     * @ORM\OneToMany(targetEntity="App\Entity\Gefra\Envio", mappedBy="operador", orphanRemoval=true)
     */
    private $envios;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gefra\SLA", mappedBy="operador", orphanRemoval=true)
     */
    private $slas;

    public function __construct()
    {
        $this->isActive = true;
        $this->uf = 'SP';
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
            'cnpj' => $this->cnpj,
            'endereco' => $this->endereco,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
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
            $this->cnpj,
            $this->endereco,
            $this->bairro,
            $this->cidade,
            $this->uf,
            $this->isActive,
            ) = unserialize($serialized);
    }

    /**
     * @return Collection|Envio[]
     */
    public function getEnvios()
    {
        return $this->envios;
    }

    public function addEnvio(Envio $envio)
    {
        if (!$this->envios->contains($envio)) {
            $this->envios[] = $envio;
            $envio->setOperador($this);
        }

        return $this;
    }

    public function removeEnvio(Envio $envio)
    {
        if ($this->envios->contains($envio)) {
            $this->envios->removeElement($envio);
            // set the owning side to null (unless already changed)
            if ($envio->getOperador() === $this) {
                $envio->setOperador(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('[%s] %s', $this->codigo, $this->nome);
    }

    /**
     * @return string
     */
    public function getBairro()
    {
        return $this->bairro;
    }

    /**
     * @param string $bairro
     * @return Operador
     */
    public function setBairro($bairro)
    {
        $this->bairro = $bairro;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndereco()
    {
        return $this->endereco;
    }

    /**
     * @param string $endereco
     * @return Operador
     */
    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;
        return $this;
    }

    /**
     * @return string
     */
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * @param string $cep
     * @return Operador
     */
    public function setCep($cep)
    {
        $this->cep = $cep;
        return $this;
    }

    /**
     * @return string
     */
    public function getCnpj()
    {
        return $this->cnpj;
    }

    /**
     * @param string $cnpj
     * @return Operador
     */
    public function setCnpj($cnpj)
    {
        $this->cnpj = $cnpj;
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
            $sla->setOperador($this);
        }

        return $this;
    }

    public function removeSla(SLA $sla): self
    {
        if ($this->slas->contains($sla)) {
            $this->slas->removeElement($sla);
            // set the owning side to null (unless already changed)
            if ($sla->getOperador() === $this) {
                $sla->setOperador(null);
            }
        }

        return $this;
    }


}
