<?php

namespace App\Entity\Main;

use App\Util\StringUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Main\UnidadeRepository")
 */
class Unidade implements \Serializable
{
    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", options={"comment"="Identificador do registro na tabela unidade"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $codigo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nome;

    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $cnpj;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $endereco;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $bairro;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $cidade;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $uf;

    /**
     * @ORM\Column(type="string", length=9, nullable=true)
     */
    private $cep;

    /**
     * @ORM\Column(type="boolean")
     */
    private $ativo;

    public function __construct()
    {
        $this->ativo = true;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

        return $this;
    }

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setCnpj(?string $cnpj): self
    {
        $this->cnpj = preg_replace("#\D#", "", $cnpj);

        return $this;
    }

    public function getEndereco(): ?string
    {
        return $this->endereco;
    }

    public function setEndereco(string $endereco): self
    {
        $this->endereco = $endereco;

        return $this;
    }

    public function getBairro(): ?string
    {
        return $this->bairro;
    }

    public function setBairro(?string $bairro): self
    {
        $this->bairro = $bairro;

        return $this;
    }

    public function getCidade(): ?string
    {
        return $this->cidade;
    }

    public function setCidade(string $cidade): self
    {
        $this->cidade = $cidade;

        return $this;
    }

    public function getUf(): ?string
    {
        return $this->uf;
    }

    public function setUf(string $uf): self
    {
        $this->uf = $uf;

        return $this;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }

    public function setCep(?string $cep): self
    {
        $this->cep = $cep;

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
            'codigo' => $this->codigo,
            'nome' => $this->nome,
            'razao_social' => $this->razao_social,
            'cnpj' => $this->cnpj,
            'endereco' => $this->endereco,
            'bairro' => $this->bairro,
            'cidade' => $this->cidade,
            'uf' => $this->uf,
            'ativo' => $this->ativo,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->codigo,
            $this->nome,
            $this->razao_social,
            $this->cnpj,
            $this->endereco,
            $this->bairro,
            $this->cidade,
            $this->uf,
            $this->ativo,
            ) = unserialize($serialized);
    }

    public function __toString(): string
    {
        return $this->nome;
    }
}
