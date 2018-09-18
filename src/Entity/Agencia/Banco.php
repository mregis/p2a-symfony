<?php

namespace App\Entity\Agencia;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as AppAssert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Agencia\BancoRepository")
 */
class Banco
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
     * @ORM\Column(type="string", length=10, unique=true)
     * @Assert\Regex("/^\d+\-?\d*$/")
     */
    private $codigo;

    /**
     * @ORM\Column(type="string", length=14, nullable=true)
     * @AppAssert\CNPJ
     */
    private $cnpj;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $is_active = true;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Agencia\Agencia", mappedBy="banco", orphanRemoval=true)
     */
    private $agencias;

    public function __construct()
    {
        $this->agencias = new ArrayCollection();
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

    public function getCodigo()
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getCnpj()
    {
        return $this->cnpj;
    }

    public function setCnpj(string $cnpj)
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    /**
     * @return Collection|Agencia[]
     */
    public function getAgencias()
    {
        return $this->agencias;
    }

    public function addAgencia(Agencia $agencia)
    {
        if (!$this->agencias->contains($agencia)) {
            $this->agencias[] = $agencia;
            $agencia->setBanco($this);
        }

        return $this;
    }

    public function removeAgencia(Agencia $agencia)
    {
        if ($this->agencias->contains($agencia)) {
            $this->agencias->removeElement($agencia);
            // set the owning side to null (unless already changed)
            if ($agencia->getBanco() === $this) {
                $agencia->setBanco(null);
            }
        }

        return $this;
    }

    public function getIsActive()
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active)
    {
        $this->is_active = $is_active;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('[%d] %s',$this->codigo, $this->nome);
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nome' => $this->nome,
            'cnpj' => $this->cnpj,
            'ativo' => $this->is_active,
            // see section on salt below
            // $this->salt,
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
            $this->is_active,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }
}
