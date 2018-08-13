<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\Gefra\Transportadora;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class TransportadoraFixtures extends Fixture
{

    public function load(ObjectManager $oManager)
    {
        // Criacao de Transportadoras
        // Novo
        $transportadora = new Transportadora();
        $transportadora->setNome('JADLOG')
            ->setCodigo('0001001')
            ->setRazaoSocial('JADLOG LOGISTICA LTDA')
            ->setCnpj('04884082000135')
            ->setCep('05157030')
            ->setEndereco('AVENIDA JORNALISTA PAULO ZINGG, 810')
            ->setBairro('JARDIM JARAGUÁ (SÃO DOMINGOS)')
            ->setCidade('SÃO PAULO')
            ->setUF('SP')
        ;
        $oManager->persist($transportadora);

        $oManager->flush();
    }


}