<?php

namespace App\Entity\Gefra;

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
     *              options={"comment"="Conhecimento de Transporte", "default"="''"},
     *              unique=true)
     */
    private $cte;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", options={"comment"="Data de Emissão do CTE"}, nullable=true)
     */
    private $dt_emissao_cte;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", options={"comment"="Data da Previsão de Coleta"}, nullable=true)
     */
    private $dt_previsao_coleta;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", options={"comment"="Data Varredura"}, nullable=false)
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
     * @ORM\Column(type="string", length=15, options={"comment"="Numero da Guia de Remessa de Material"})
     */
    private $grm;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=2, options={"comment"="Valor do Conhecimento - CTE"})
     */
    private $valor;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"comment"="Quantidade de Volumes do envio"})
     */
    private $qt_vol;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=4, options={"comment"="Peso total dos volumes em KG"})
     */
    private $peso;

    /**
     * @var Operador
     * @ORM\ManyToOne(targetEntity="App\Entity\Gefra\Operador", inversedBy="envios")
     * @ORM\JoinColumn(name="operador_id", nullable=false)
     */
    private $operador;

    /**
     * @var Juncao
     * @ORM\ManyToOne(targetEntity="App\Entity\Gefra\Juncao", inversedBy="envios")
     * @ORM\JoinColumn(name="juncao_id", nullable=false)
     */
    private $juncao;

    /**
     * @var string
     * @ORM\Column(type="string", options={"comment"="Numero da Solicitacao"})
     */
    private $solicitacao;


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
            'dt_cte' => $this->dt_emissao_cte,
            'dt_previsao_coleta' => $this->dt_previsao_coleta,
            'dt_varredura' => $this->dt_varredura,
            'grm' => $this->grm,
            'peso' => $this->peso,
            'qt_vol' => $this->qt_vol,
            'valor' => $this->valor,
            'dt_previsao_entrega' => $this->dt_previsao_entrega,
            'dt_entrega' => $this->dt_entrega,
            'solicitacao' => $this->solicitacao,
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
            $this->dt_emissao_cte,
            $this->dt_previsao_coleta,
            $this->dt_varredura,
            $this->grm,
            $this->peso,
            $this->qt_vol,
            $this->valor,
            $this->dt_previsao_entrega,
            $this->dt_entrega,
            $this->solicitacao,
            ) = unserialize($serialized);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->cte;
    }

    public function getCte(): ?string
    {
        return $this->cte;
    }

    public function setCte(string $cte): self
    {
        $this->cte = $cte;

        return $this;
    }

    public function getDtEmissao(): ?\DateTimeInterface
    {
        return $this->dt_emissao;
    }

    public function setDtEmissao(\DateTimeInterface $dt_emissao): self
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

    public function getPeso()
    {
        return $this->peso;
    }

    public function setPeso($peso): self
    {
        $this->peso = $peso;

        return $this;
    }

    public function getSolicitacao(): ?string
    {
        return $this->solicitacao;
    }

    public function setSolicitacao(string $solicitacao): self
    {
        $this->solicitacao = $solicitacao;

        return $this;
    }

    public function getOperador(): ?Operador
    {
        return $this->operador;
    }

    public function setOperador(?Operador $operador): self
    {
        $this->operador = $operador;

        return $this;
    }

    public function getDestino(): ?Juncao
    {
        return $this->destino;
    }

    public function setDestino(?Juncao $destino): self
    {
        $this->destino = $destino;

        return $this;
    }

    public function getDtPrevisaoColeta(): ?\DateTimeInterface
    {
        return $this->dt_previsao_coleta;
    }

    public function setDtPrevisaoColeta(\DateTimeInterface $dt_previsao_coleta): self
    {
        $this->dt_previsao_coleta = $dt_previsao_coleta;

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

    public function setDtVarredura(\DateTimeInterface $dt_varredura): self
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

    public function setJuncao(?Juncao $juncao): self
    {
        $this->juncao = $juncao;

        return $this;
    }
}
