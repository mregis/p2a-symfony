<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 10/05/2018
 * Time: 21:09
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UFType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                'Sudeste' => array(
                    'São Paulo' => 'SP',
                    'Rio de Janeiro' => 'RJ',
                    'Minas Gerais' => 'MG',
                    'Espírito Santo' => 'ES',
                ),
                'Sul' => array(
                    'Rio Grande do Sul' => 'RS',
                    'Paraná' => 'PR',
                    'Santa Catarina' => 'SC',),
                'Nordeste' => array(
                    'Alagoas' => 'AL',
                    'Bahia' => 'BA',
                    'Ceará' => 'CE',
                    'Maranhão' => 'MA',
                    'Paraíba' => 'PB',
                    'Pernambuco' => 'PE',
                    'Piauí' => 'PI',
                    'Rio Grande do Norte' => 'RN',
                    'Sergipe' => 'SE',),
                'Centro-Oeste' => array(
                    'Distrito Federal' => 'DF',
                    'Goiás' => 'GO',
                    'Mato Grosso' => 'MT',
                    'Mato Grosso do Sul' => 'MS',),
                'Norte' => array(
                    'Amazonas' => 'AM',
                    'Amapá' => 'AP',
                    'Pará' => 'PA',
                    'Rondônia' => 'RO',
                    'Roraima' => 'RR',
                    'Acre' => 'AC',
                    'Tocantins' => 'TO'),
            ),
            'placeholder' => 'choice-field.placeholder'
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}