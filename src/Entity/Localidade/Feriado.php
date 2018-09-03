<?php

namespace App\Entity\Localidade;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Localidade\FeriadoRepository")
 */
class Feriado
{

    const TIPOFERIADO_NACIONAL = 'nacional';
    const TIPOFERIADO_ESTADUAL = 'estadual';
    const TIPOFERIADO_MUNICIPAL = 'municipal';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dt_feriado;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $descricao;

    /**
     * @var string
     * @ORM\Column(type="string", length=15)
     */
    private $tipo;

    /**
     * @var UF
     * @ORM\ManyToOne(targetEntity="UF", inversedBy="feriados")
     */
    private $uf;

    /**
     * @var Cidade
     * @ORM\ManyToOne(targetEntity="Cidade", inversedBy="feriados")
     * @ORM\JoinColumn(nullable=true)
     */
    private $local;

    public function getId()
    {
        return $this->id;
    }

    public function getDtFeriado(): ?\DateTimeInterface
    {
        return $this->dt_feriado;
    }

    public function setDtFeriado(\DateTimeInterface $dt_feriado): self
    {
        $this->dt_feriado = $dt_feriado;

        return $this;
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

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getLocal(): ?Cidade
    {
        return $this->local;
    }

    public function setLocal(?Cidade $local): self
    {
        $this->local = $local;

        return $this;
    }

    public function getUf(): ?UF
    {
        return $this->uf;
    }

    public function setUf(?UF $uf): self
    {
        $this->uf = $uf;

        return $this;
    }
}
