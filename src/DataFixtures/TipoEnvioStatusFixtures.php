<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\Gefra\TipoEnvioStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TipoEnvioStatusFixtures extends Fixture
{

    public function load(ObjectManager $oManager)
    {
        // Criacao dos estados
        // Novo
        $tipoEnvioStatus = new TipoEnvioStatus();
        $tipoEnvioStatus->setNome('NOVO')
            ->setDescricao('Envio foi criado e ainda não sofreu nenhuma interação.');
        $oManager->persist($tipoEnvioStatus);

        $tipoEnvioStatus = new TipoEnvioStatus();
        $tipoEnvioStatus->setNome('ATRASADO')
            ->setDescricao('Envio ainda não foi marcado como entregue e data prevista para entrega já passou.');
        $oManager->persist($tipoEnvioStatus);

        $tipoEnvioStatus = new TipoEnvioStatus();
        $tipoEnvioStatus->setNome('EM ANDAMENTO')
            ->setDescricao('Envio está sendo preparado.');
        $oManager->persist($tipoEnvioStatus);

        $tipoEnvioStatus = new TipoEnvioStatus();
        $tipoEnvioStatus->setNome('EM TRANSITO')
            ->setDescricao('Envio já foi despachado.');
        $oManager->persist($tipoEnvioStatus);

        $tipoEnvioStatus = new TipoEnvioStatus();
        $tipoEnvioStatus->setNome('ENTREGUE NO PRAZO')
            ->setDescricao('Envio foi entregue dentro do período acordado.');
        $oManager->persist($tipoEnvioStatus);

        $tipoEnvioStatus = new TipoEnvioStatus();
        $tipoEnvioStatus->setNome('ENTREGUE FORA DO PRAZO')
            ->setDescricao('Envio foi entregue fora do período acordado.');
        $oManager->persist($tipoEnvioStatus);

        $tipoEnvioStatus = new TipoEnvioStatus();
        $tipoEnvioStatus->setNome('REPROGRAMADO')
            ->setDescricao('Envio teve a data prevista de entrega alterada.');
        $oManager->persist($tipoEnvioStatus);

        $oManager->flush();
    }


}