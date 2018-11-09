<?php

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Main\UploadDataFileRepository")
 * @ORM\Table(name="arquivos_dados", options={"comment"="Auxiliar do carregamento de arquivos para processamento"})
 */
class UploadDataFile
{

    // UploadDataFile Processing Satus
    const NEW_SEND = 'NEW_SEND';
    const IN_PROGRESS = 'IN_PROGRESS';
    const FINISHED_OK = 'FINISHED_OK';
    const FINISHED_ERROR = 'FINISHED_ERROR';
    const ABORTED = 'ABORTED';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer", options={"comment"="Identificador do registro na tabela arquivo_envio"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=8, unique=true, options={"comment"="Identificador unico do arquivo carregado"})
     */
    private $hashid;

    /**
     * @ORM\Column(type="string", length=50, options={"comment"="Tipos de Arquivos"})
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, options={"comment"="Descricao do arquivo"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, options={"comment"="Caminho fisico para o arquivo no sistema"})
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=40, options={"comment"="Estado do processamento"})
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", options={"comment"="Data que o arquivo foi carregado"})
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment"="Data do inicio do processamento"})
     */
    private $processing_started_at;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment"="Data do fim do processamento"})
     */
    private $processing_ended_at;

    /**
     * @ORM\Column(type="string", length=255, options={"comment"="ID do usuario que fez o carregamento"})
     */
    private $uploaded_by;

    /**
     *
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getHashid(): ?string
    {
        return $this->hashid;
    }

    public function setHashid(string $hashid): self
    {
        $this->hashid = $hashid;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
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

    public function getProcessingStartedAt(): ?\DateTimeInterface
    {
        return $this->processing_started_at;
    }

    public function setProcessingStartedAt(?\DateTimeInterface $processing_started_at): self
    {
        $this->processing_started_at = $processing_started_at;

        return $this;
    }

    public function getProcessingEndedAt(): ?\DateTimeInterface
    {
        return $this->processing_ended_at;
    }

    public function setProcessingEndedAt(?\DateTimeInterface $processing_ended_at): self
    {
        $this->processing_ended_at = $processing_ended_at;

        return $this;
    }

    public function getUploadedBy(): ?String
    {
        return $this->uploaded_by;
    }

    public function setUploadedBy(?string $uploaded_by): self
    {
        $this->uploaded_by = $uploaded_by;

        return $this;
    }

}
