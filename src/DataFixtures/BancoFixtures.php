<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\Agencia\Banco;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class BancoFixtures extends Fixture
{

    public function load(ObjectManager $oManager)
    {
        // Criacao do Banco Bradesco
        $banco = new Banco();
        $banco->setNome('Banco Bradesco S.A.')
            ->setCodigo('237')
            ->setCnpj('60746948000112');

        $oManager->persist($banco);

        // Criacao do Banco Bradescard (IBI)
        $banco = new Banco();
        $banco->setNome('Banco Bradescard S.A.')
            ->setCodigo('036')
            ->setCnpj('04184779000101');

        $oManager->persist($banco);

        // Criacao do Banco Losango
        $banco = new Banco();
        $banco->setNome(' Banco Losango S.A - Banco Múltiplo')
            ->setCodigo('99Multiplo')
            ->setCnpj('332543190001');

        $oManager->persist($banco);

        $oManager->flush();
    }


}