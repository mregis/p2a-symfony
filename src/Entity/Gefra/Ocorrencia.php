<?php

namespace App\Entity\Gefra;

use App\Entity\Main\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Gefra\OcorrenciaRepository")
 */
class Ocorrencia
{

    const TIPO_UPDATE = 'ATUALIZACAO';
    const TIPO_CREATE = 'CRIACAO';
    const TIPO_CANCEL = 'CANCELAMENTO';

    /**
     * @var string
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid", options={"comment"="Identificador do registro"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", name="criado_em")
     */
    private $created_at;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $descricao;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Gefra\Envio", inversedBy="ocorrencias")
     * @ORM\JoinColumn(nullable=false)
     */
    private $envio;

    /**
     * @ORM\Column(type="string", length=40, options={"comment"="Usuario autenticado que alterou a ocorrencia"})
     */
    private $usuario;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

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

    public function setDescricao(string $descricao): self
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getEnvio(): ?Envio
    {
        return $this->envio;
    }

    public function setEnvio(?Envio $envio): self
    {
        $this->envio = $envio;

        return $this;
    }

    public function getUsuario(): ?string
    {
        return $this->usuario;
    }

    public function setUsuario(?string $usuario): self
    {
        $this->usuario = $usuario;

        return $this;
    }
}
