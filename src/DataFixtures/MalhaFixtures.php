<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\Malote\Malha;
use App\Util\StringUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class MalhaFixtures extends Fixture
{

    public function load(ObjectManager $oManager)
    {
        $nomes_malha = ['SANTO ANGELO','NOVA XAVANTINA','CANARANA','PORTO ALEGRE DO NORT','SÃO FÉLIX DO ARAGUAI',
            'BARRA DO GARÇAS','SALVADOR','ARACAJU','EUNAPOLIS','IRECE','IPATINGA','FEIRA DE SANTANA',
            'JUIZ DE FORA','UBERABA','GOVERNADOR VALADARES','JOAO MONLEVADE','ITABUNA',
            'PAULO AFONSO','VITORIA DA CONQUISTA','JUAZEIRO','ARAÇATUBA','SANTA MARIA DA VITOR',
            'CURVELO','BARREIRAS','GUANAMBI','CONSELHEIRO LAFAIETE','MANHUACU',
            'TEOFILO OTONI','SEABRA','BOM DESPACHO','MONTES CLAROS','BAURU','UBERLANDIA',
            'BELO HORIZONTE','PATOS DE MINAS','RIBEIRAO PRETO','VARGINHA','MANAUS URB',
            'COSTA MARQUES','MACAPA URB','SANTAREM','ARCOVERDE','CAMPO MAIOR','JOAO PESSOA',
            'PARNAIBA','BOA VISTA','URBANO MACAPA','MOSSORO','SOROCABA','OURINHOS','CAMPO GRANDE',
            'MACAPA','SOBRAL','BALSAS','PARAGOMINAS','BLUMENAU','BRASILIA','SÃO JOSÉ DOS CAMPOS',
            'PORTO VELHO URB','RECIFE URB','SÃO PAULO','CASCAVEL','CAPANEMA','PEDRA BRANCA',
            'REDENCAO','IGUATU','PRESIDENTE DUTRA','MARABA','JUAZEIRO DO NORTE','ALTAMIRA',
            'PELOTAS','PORTO VELHO','PICOS','DELMIRO GOUVEIA','NATAL','ARAPIRACA',
            'PATOS','GUARATINGUETÁ','MACEIO','CAMPINAS','CHAPECÓ','LARANJEIRAS DO SUL',
            'DOURADOS','GOIANIA','IMPERATRIZ','SOLEDADE','SANTA MARIA','PASSO FUNDO',
            'URBANO MANAUS','FORTALEZA URB','ARIQUEMES','BELEM URB','CAMPINA GRANDE',
            'FLORIANO','RECIFE','TERESINA','URUACU','CRICIUMA','RONDONÓPOLIS',
            'LONDRINA','CACERES','BELEM','RIO BRANCO','SALGUEIRO','JI PARANA',
            'SAO LUIS','PONTES E LACERDA','PORANGATU','MARINGA','FLORIANOPOLIS',
            'REGISTRO','MALHA REGISTRO','VÁRZEA GRANDE','ALEGRETE','LAJEADO',
            'CARUARU','PRESIDENTE PRUDENTE','SAO JOSE DO RIO PRET','CAMPO NOVO DO PARECI',
            'GURUPI','CURITIBANOS','LUCAS DO RIO VERDE','PORTO ALEGRE','CAXIAS DO SUL',
            'SANTA INES','JOINVILLE','SINOP','CAMPOS BELOS','COLIDER','CACOAL','FORTALEZA',
            'CASTANHAL','ALTA FLORESTA','PONTA GROSSA','RIO VERDE','JURUENA','SANTOS',
            'MANAUS','VILHENA','JUINA','PALMAS','CURITIBA','JUARA','PINHEIRO','UNIAO DA VITORIA',
            'ARAGUAINA','CACHOEIRO DE ITAPEMI','CAMPOS URB','CAMPOS INT','LINHARES INT',
            'LINHARES URB','RIO DE JANEIRO CAP','RIO DE JANEIRO INT','VENDA NOVA DO IMIGRA',
            'VITORIA INT','VITORIA URB'];
        // Criação de Malhas

        foreach ($nomes_malha as $nome_malha)
        {
            $malha_repo = $oManager->getRepository(Malha::class);
            if (!$malha_repo->findOneBy(
                    ['nome_canonico' => StringUtils::slugify($nome_malha)]
                )
            ) {
                $malha = new Malha();
                $malha->setNome($nome_malha);
                $oManager->persist($malha);
            } // ignora já existentes
        }

        $oManager->flush();
    }


}