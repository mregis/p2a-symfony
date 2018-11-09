<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\Main\Unidade;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UnidadeFixtures extends Fixture
{

    public function load(ObjectManager $oManager)
    {
        // Criacao de Unidades
        // Novo
        $unidade = new Unidade();
        $unidade->setNome('Address São Paulo (Matriz)')
            ->setCodigo('001')
            ->setCnpj('58720731000191')
            ->setCep('05311030')
            ->setEndereco('Rua Mergenthaler 1177')
            ->setBairro('Vila Leopoldina')
            ->setCidade('SÃO PAULO')
            ->setUF('SP')
        ;
        $oManager->persist($unidade);

        $unidade = new Unidade();
        $unidade->setNome('Address Rio de Janeiro')
            ->setCodigo('002')
            ->setCnpj('58720731001759')
            ->setCep('13295000')
            ->setEndereco('Rua Senador Bernardo Monteiro 215')
            ->setBairro('Benfica')
            ->setCidade('Rio de Janeiro')
            ->setUF('RJ')
        ;
        $oManager->persist($unidade);

        $unidade = new Unidade();
        $unidade->setNome('JAD TAXI AEREO')
            ->setCodigo('0001003')
            ->setRazaoSocial('JAD TAXI AEREO LTDA')
            ->setCnpj('02017835000180')
            ->setCep('13212010')
            ->setEndereco('Av Emilio Antonon, 901 D')
            ->setBairro('CHÁCARA AEROPORTO')
            ->setCidade('JUNDIAÍ')
            ->setUF('SP')
        ;
        $oManager->persist($unidade);

        $unidade = new Unidade();
        $unidade->setNome('GRITSCH')
            ->setCodigo('0001004')
            ->setRazaoSocial('TRANSPORTES GRITSCH LTDA')
            ->setCnpj('90739624000380')
            ->setCep('02120000')
            ->setEndereco('R Curuca, 473')
            ->setBairro('VILA MARIA BAIXA')
            ->setCidade('SÃO PAULO')
            ->setUF('SP')
        ;
        $oManager->persist($unidade);

        $unidade = new Unidade();
        $unidade->setCodigo('0001005')
            ->setCnpj('57317133000294')
            ->setNome('JAD LOCADORA')
            ->setRazaoSocial('JAD LOCADORA & TRANSPORTES LTDA')
            ->setEndereco('R Mauricio Oscar Da R Silva, 49')
            ->setCidade('RIBEIRÃO PRETO')
            ->setUF('SP')
            ->setBairro('RESIDENCIAL E COMERCIAL PALMARES')
            ->setCep('14092580')
        ;
        $oManager->persist($unidade);

        $unidade = new Unidade();
        $unidade->setNome('OLIVEIRA')
            ->setCodigo('0001006')
            ->setRazaoSocial('TRANSPORTES OLIVEIRA')
            ->setCidade(null)
            ->setUF(null)
        ;
        $oManager->persist($unidade);

        $unidade = new Unidade();
        $unidade->setNome('RODOSOUZA')
            ->setCodigo('0001007')
            ->setRazaoSocial('RODOSOUZA TRANSPORTES LTDA - ME')
            ->setCnpj('07947460000170')
            ->setCep('04837140')
            ->setEndereco('R Mesopotamia, 211')
            ->setBairro('VILA QUINTANA')
        ;
        $oManager->persist($unidade);

        $unidade = new Unidade();
        $unidade->setNome('OPR')
            ->setCodigo('0001008')
            ->setRazaoSocial('OPR-TRANSPORTES BEBEDOURO LTDA - ME')
            ->setCnpj('05886766000139')
            ->setCep('14701000')
            ->setEndereco('R Coronel Conrado Caldeira, 255, A')
            ->setBairro('CONRADO CALDEIRA')
            ->setCidade('BEBEDOURO')
            ->setUF('SP')
        ;
        $oManager->persist($unidade);

        $unidade = new Unidade();
        $unidade->setNome('DV3')
            ->setCodigo('0001009')
            ->setRazaoSocial('DV3 SOLUÇÕES LOGÍSTICAS LTDA')
            ->setCnpj('57317133000103')
            ->setCep('02713000')
            ->setEndereco('Avenida Nossa Senhora do Ó, 1453')
            ->setBairro('Limão')
        ;
        $oManager->persist($unidade);

        $unidade = new Unidade();
        $unidade->setNome('DV3')
            ->setCodigo('0001009')
            ->setRazaoSocial('DV3 SOLUÇÕES LOGÍSTICAS LTDA')
            ->setCnpj('57317133000103')
            ->setCep('02713000')
            ->setEndereco('Avenida Nossa Senhora do Ó, 1453')
            ->setBairro('Limão')
        ;
        $oManager->persist($unidade);
        $oManager->flush();
    }


}