<?php

namespace App\Entity\Gefra;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Gefra\EnvioRepository")
 */
class Envio implements \Serializable
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
     * @ORM\Column(type="string", length=15,
     *              options={"comment"="Conhecimento de Transporte"}, nullable=true)
     */
    private $cte;

    /**
     * @var \DateTime
     * @ORM\Column(name="criado_em", type="datetime", options={"comment"="Data de Criação do Envio"})
     */
    private $created_at;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", options={"comment"="Data de Emissão do CTE"}, nullable=true)
     */
    private $dt_emissao_cte;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", options={"comment"="Data Efetiva da Coleta"}, nullable=true)
     */
    private $dt_coleta;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", options={"comment"="Data Informada da Coleta - Previsao"}, nullable=true)
     */
    private $dt_previsao_coleta;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", options={"comment"="Data Varredura"}, nullable=true)
     */
    private $dt_varredura;


    /**
     * @var \DateTime
     * @ORM\Column(type="date", options={"comment"="Data da Previsão de Entrega"}, nullable=true)
     */
    private $dt_previsao_entrega;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", options={"comment"="Data da Previsão de Entrega"}, nullable=true)
     */
    private $dt_entrega;

    /**
     * @var string
     * @ORM\Column(type="string", length=15,
     *              options={"comment"="Numero da Guia de Remessa de Material"}, unique=true)
     */
    private $grm;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=10, scale=2, options={"comment"="Valor do Conhecimento - CTE"})
     * @Assert\GreaterThanOrEqual(0.50)
     */
    private $valor;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"comment"="Quantidade de Volumes do envio", "default"="1"})
     * @Assert\GreaterThanOrEqual(1)
     */
    private $qt_vol;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=9, scale=4, options={"comment"="Peso total dos volumes em KG"})
     * @Assert\GreaterThanOrEqual(0.150)
     */
    private $peso;

    /**
     * @var Operador
     * @ORM\ManyToOne(targetEntity="App\Entity\Gefra\Operador", inversedBy="envios")
     * @ORM\JoinColumn(name="operador_id", nullable=false)
     */
    private $operador;

    /**
     * @var Transportadora
     * @ORM\ManyToOne(targetEntity="App\Entity\Gefra\Transportadora", inversedBy="envios")
     * @ORM\JoinColumn(name="transportadora_id", nullable=false)
     */
    private $transportadora;

    /**
     * @var Juncao
     * @ORM\ManyToOne(targetEntity="App\Entity\Gefra\Juncao", inversedBy="envios")
     * @ORM\JoinColumn(name="juncao_id", nullable=false)
     */
    private $juncao;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Gefra\Ocorrencia", mappedBy="envio", orphanRemoval=true)
     */
    private $ocorrencias;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $recebedor;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $doc_recebedor;

    /**
     * @ORM\Column(type="string", length=16, options={"comment"="Lote a qual o Envio esta ligado"})
     */
    private $lote;

    /**
     * @var string
     * @ORM\Column(type="string", options={"comment"="Numero da Solicitacao"}, nullable=true, length=20)
     */
    private $solicitacao;

    /**
     * @ORM\ManyToOne(targetEntity="TipoEnvioStatus", inversedBy="envios")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(type="text", options={"comment"="Numero da Solicitacao"}, nullable=true)
     */
    private $observacao;

    public function __construct()
    {
        $this->ocorrencias = new ArrayCollection();
        $this->qt_vol = 1;
        $this->created_at = new \DateTime();
    }


    public function getId()
    {
        return $this->id;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            'id' => $this->id,
            'cte' => $this->cte,
            'juncao' => unserialize($this->getJuncao()->serialize()),
            'operador' => unserialize($this->getOperador()->serialize()),
            'created_at' => $this->created_at,
            'dt_emissao_cte' => $this->dt_emissao_cte,
            'dt_coleta' => $this->dt_coleta,
            'dt_previsao_coleta' => $this->dt_previsao_coleta,
            'dt_varredura' => $this->dt_varredura,
            'grm' => $this->grm,
            'peso' => $this->peso,
            'qt_vol' => $this->qt_vol,
            'valor' => $this->valor,
            'dt_previsao_entrega' => $this->dt_previsao_entrega,
            'dt_entrega' => $this->dt_entrega,
            'recebdor' => $this->recebedor,
            'doc_recebdor' => $this->doc_recebedor,
            'solicitacao' => $this->solicitacao,
            'lote' => $this->lote,
            'status' => unserialize($this->getStatus()->serialize()),
            'observacao' => $this->observacao,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
             $this->id,
            $this->cte,
            $this->juncao,
            $this->operador,
            $this->created_at,
            $this->dt_emissao_cte,
            $this->dt_coleta,
            $this->dt_previsao_coleta,
            $this->dt_varredura,
            $this->grm,
            $this->peso,
            $this->qt_vol,
            $this->valor,
            $this->dt_previsao_entrega,
            $this->dt_entrega,
            $this->recebedor,
            $this->doc_recebedor,
            $this->solicitacao,
            $this->lote,
            $this->status,
            $this->observacao,
            ) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->grm;
    }

    public function getCte(): ?string
    {
        return $this->cte;
    }

    public function setCte(?string $cte): self
    {
        $this->cte = $cte;

        return $this;
    }

    public function getDtEmissao(): ?\DateTimeInterface
    {
        return $this->dt_emissao;
    }

    public function setDtEmissao(?\DateTimeInterface $dt_emissao): self
    {
        $this->dt_emissao = $dt_emissao;

        return $this;
    }

    public function getGrm(): ?string
    {
        return $this->grm;
    }

    public function setGrm(string $grm): self
    {
        $this->grm = $grm;

        return $this;
    }

    public function setDocumento(string $grm): self
    {
        $this->grm = $grm;
        return $this;
    }

    public function getValor()
    {
        return $this->valor;
    }

    public function setValor($valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getQtVol(): ?int
    {
        return $this->qt_vol;
    }

    public function setQtVol(int $qt_vol): self
    {
        $this->qt_vol = $qt_vol;
        return $this;
    }

    public function setVolume(int $qt_vol): self
    {
        $this->qt_vol = $qt_vol;
        return $this;
    }

    public function getPeso()
    {
        return $this->peso;
    }

    public function setPeso($peso): self
    {
        $this->peso = $peso;

        return $this;
    }

    public function getOperador(): ?Operador
    {
        return $this->operador;
    }

    public function setOperador(Operador $operador): self
    {
        $this->operador = $operador;

        return $this;
    }

    public function getDtColeta(): ?\DateTimeInterface
    {
        return $this->dt_coleta;
    }

    public function setDtColeta(?\DateTimeInterface $dt_coleta = null): self
    {
        $this->dt_coleta = $dt_coleta;

        return $this;
    }

    public function getDtPrevisaoEntrega(): ?\DateTimeInterface
    {
        return $this->dt_previsao_entrega;
    }

    public function setDtPrevisaoEntrega(\DateTimeInterface $dt_previsao_entrega): self
    {
        $this->dt_previsao_entrega = $dt_previsao_entrega;

        return $this;
    }

    public function getDtVarredura(): ?\DateTimeInterface
    {
        return $this->dt_varredura;
    }

    public function setDtVarredura(?\DateTimeInterface $dt_varredura): self
    {
        $this->dt_varredura = $dt_varredura;

        return $this;
    }

    public function getDtEmissaoCte(): ?\DateTimeInterface
    {
        return $this->dt_emissao_cte;
    }

    public function setDtEmissaoCte(?\DateTimeInterface $dt_emissao_cte): self
    {
        $this->dt_emissao_cte = $dt_emissao_cte;

        return $this;
    }

    public function getJuncao(): ?Juncao
    {
        return $this->juncao;
    }

    public function setJuncao(Juncao $juncao): self
    {
        $this->juncao = $juncao;

        return $this;
    }

    /**
     * @return Collection|Ocorrencia[]
     */
    public function getOcorrencias(): Collection
    {
        return $this->ocorrencias;
    }

    public function addOcorrencia(Ocorrencia $ocorrencia): self
    {
        if (!$this->ocorrencias->contains($ocorrencia)) {
            $this->ocorrencias[] = $ocorrencia;
            $ocorrencia->setEnvio($this);
        }

        return $this;
    }

    public function removeOcorrencia(Ocorrencia $ocorrencia): self
    {
        if ($this->ocorrencias->contains($ocorrencia)) {
            $this->ocorrencias->removeElement($ocorrencia);
            // set the owning side to null (unless already changed)
            if ($ocorrencia->getEnvio() === $this) {
                $ocorrencia->setEnvio(null);
            }
        }

        return $this;
    }

    public function getDtEntrega(): ?\DateTimeInterface
    {
        return $this->dt_entrega;
    }

    public function setDtEntrega(?\DateTimeInterface $dt_entrega): self
    {
        $this->dt_entrega = $dt_entrega;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getRecebedor(): ?string
    {
        return $this->recebedor;
    }

    public function setRecebedor(?string $recebedor): self
    {
        $this->recebedor = $recebedor;

        return $this;
    }

    public function getDocRecebedor(): ?string
    {
        return $this->doc_recebedor;
    }

    public function setDocRecebedor(?string $doc_recebedor): self
    {
        $this->doc_recebedor = $doc_recebedor;

        return $this;
    }

    public function getDtPrevisaoColeta(): \DateTimeInterface
    {
        return $this->dt_previsao_coleta;
    }

    public function setDtPrevisaoColeta(\DateTimeInterface $dt_previsao_coleta): self
    {
        $this->dt_previsao_coleta = $dt_previsao_coleta;

        return $this;
    }

    public function getTransportadora(): ?Transportadora
    {
        return $this->transportadora;
    }

    public function setTransportadora(Transportadora $transportadora): self
    {
        $this->transportadora = $transportadora;

        return $this;
    }

    public function getStatus(): TipoEnvioStatus
    {
        return $this->status;
    }

    public function setStatus(TipoEnvioStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getLote(): ?string
    {
        return $this->lote;
    }

    public function setLote(string $lote): self
    {
        $this->lote = $lote;

        return $this;
    }

    public function getSolicitacao(): ?string
    {
        return $this->solicitacao;
    }

    public function setSolicitacao(?string $solicitacao): self
    {
        $this->solicitacao = $solicitacao;

        return $this;
    }

    public function getObservacao(): ?string
    {
        return $this->observacao;
    }

    public function setObservacao(?string $observacao): self
    {
        $this->observacao = $observacao;

        return $this;
    }
 
}
