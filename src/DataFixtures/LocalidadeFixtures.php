<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 06/04/2018
 * Time: 20:01
 */

namespace App\DataFixtures;

use App\Entity\Localidade\Regiao;
use App\Entity\Localidade\UF;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LocalidadeFixtures extends Fixture
{

    public function load(ObjectManager $oManager)
    {
        // Criando Regioes
        $regioes = ['S' => 'REGIÃO SUL', 'SE' => 'REGIÃO SUDESTE',
            'CO' => 'REGIÃO CENTRO OESTE', 'NE' => 'REGIÃO NORDESTE', 'N' => 'REGIÃO NORTE'];
        foreach ($regioes as $sigla => $nome) {
            $regiao = new Regiao();
            $regiao->setNome($nome)
                ->setSigla($sigla);
            $oManager->persist($regiao);
        }

        // Criando UFs
        $estados = array(
            'SE' => array(
                'São Paulo' => 'SP',
                'Rio de Janeiro' => 'RJ',
                'Minas Gerais' => 'MG',
                'Espírito Santo' => 'ES',
            ),
            'S' => array(
                'Rio Grande do Sul' => 'RS',
                'Paraná' => 'PR',
                'Santa Catarina' => 'SC',),
            'NE' => array(
                'Alagoas' => 'AL',
                'Bahia' => 'BA',
                'Ceará' => 'CE',
                'Maranhão' => 'MA',
                'Paraíba' => 'PB',
                'Pernambuco' => 'PE',
                'Piauí' => 'PI',
                'Rio Grande do Norte' => 'RN',
                'Sergipe' => 'SE',),
            'CO' => array(
                'Distrito Federal' => 'DF',
                'Goiás' => 'GO',
                'Mato Grosso' => 'MT',
                'Mato Grosso do Sul' => 'MS',),
            'N' => array(
                'Amazonas' => 'AM',
                'Amapá' => 'AP',
                'Pará' => 'PA',
                'Rondônia' => 'RO',
                'Roraima' => 'RR',
                'Acre' => 'AC',
                'Tocantins' => 'TO'),
        );

        $regiaoRepo = $oManager->getRepository(Regiao::class);
        foreach ($estados as $_regiao => $ufs) {
            $regiao = $regiaoRepo->findOneBy(array('sigla' => $_regiao));
            foreach ($ufs as $nome => $sigla) {
                $uf = new UF();
                $uf->setNome($nome)
                    ->setSigla($sigla)
                    ->setRegiao($regiao);
                $regiao->addUF($uf);
                $oManager->persist($uf);
            }
            $oManager->persist($regiao);
        }

        $oManager->flush();
    }


}