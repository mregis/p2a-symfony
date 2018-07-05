<?php

namespace App\Entity\Localidade;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Localidade\CidadeRepository")
 */
class Cidade
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=100)
     */
    private $nome;

    /**
     * @var string
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @Assert\Length(min = 4, max = 20)
     */
    private $abreviacao;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $codigo;

    /**
     * @var UF
     * @ORM\ManyToOne(targetEntity="UF", inversedBy="cidades")
     */
    private $uf;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $criado_em;

    /**
     * @var boolean
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $ativo;

    public function __construct()
    {
        $this->criado_em = new \DateTime('now');
        $this->ativo = true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Cidade
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param string $nome
     * @return Cidade
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
        return $this;
    }

    /**
     * @return string
     */
    public function getAbreviacao()
    {
        return $this->abreviacao;
    }

    /**
     * @param string $abreviacao
     * @return Cidade
     */
    public function setAbreviacao($abreviacao)
    {
        $this->abreviacao = $abreviacao;
        return $this;
    }

    /**
     * @return int
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param int $codigo
     * @return Cidade
     */
    public function setCodIBGE($codigo)
    {
        $this->codigo = $codigo;
        return $this;
    }

    /**
     * @return UF
     */
    public function getUf()
    {
        return $this->uf;
    }

    /**
     * @param UF $uf
     * @return Cidade
     */
    public function setUf(UF $uf)
    {
        $this->uf = $uf;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isAtivo()
    {
        return $this->ativo;
    }

    /**
     * @param boolean $ativo
     * @return UF
     */
    public function setAtivo($ativo)
    {
        $this->ativo = $ativo;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCriadoEm()
    {
        return $this->criado_em;
    }

    /**
     * @param DateTime $criado_em
     * @return Cidade
     */
    public function setCriadoEm(\DateTime $criado_em)
    {
        $this->criado_em = $criado_em;
        return $this;
    }

}
