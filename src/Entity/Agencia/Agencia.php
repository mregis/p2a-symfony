<?php

namespace App\Entity\Agencia;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Agencia\AgenciaRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"banco_id", "codigo"})})
 */
class Agencia
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
     * @ORM\Column(type="string", length=10)
     * @Assert\Regex("/^\d+$/")
     */
    private $codigo;

    /**
     * @ORM\Column(type="smallint")
     *
     * @Assert\Range(min = 0, max = 9)
     */
    private $dv;
    
    /**
     * @ORM\Column(type="string", length=9)
     * @Assert\Regex("/\d{5}\-?\d{3}/")
     */
    private $cep;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $logradouro;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $numeral;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $complemento;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $bairro;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $cidade;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $uf;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Agencia\Banco", inversedBy="agencias")
     * @ORM\JoinColumn(name="banco_id", nullable=false)
     */
    private $banco;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $isActive;

    public function __construct()
    {
        $this->isActive = true;
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

    public function getDv()
    {
        return $this->dv;
    }

    public function setDv(int $dv)
    {
        $this->dv = $dv;

        return $this;
    }

    public function getCep()
    {
        return $this->cep;
    }

    public function setCep(string $cep)
    {
        $this->cep = $cep;

        return $this;
    }

    public function getLogradouro()
    {
        return $this->logradouro;
    }

    public function setLogradouro(string $logradouro)
    {
        $this->logradouro = $logradouro;

        return $this;
    }

    public function getNumeral()
    {
        return $this->numeral;
    }

    public function setNumeral(string $numeral)
    {
        $this->numeral = $numeral;

        return $this;
    }

    public function getComplemento()
    {
        return $this->complemento;
    }

    public function setComplemento(string $complemento)
    {
        $this->complemento = $complemento;

        return $this;
    }

    public function getBairro()
    {
        return $this->bairro;
    }

    public function setBairro(string $bairro)
    {
        $this->bairro = $bairro;

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

    public function getBanco()
    {
        return $this->banco;
    }

    public function setBanco(Banco $banco)
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
}
