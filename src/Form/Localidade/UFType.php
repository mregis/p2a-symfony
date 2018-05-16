<?php

namespace App\Form\Localidade;

use App\Entity\Localidade\Regiao;
use App\Entity\Localidade\UF;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UFType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('regiao', EntityType::class, array('class' => Regiao::class,
                'choice_label' => function ($regiao) {
                    return $regiao->getNome() . ' [' . $regiao->getSigla() . ']';
                },
                'placeholder' => 'choice-field.placeholder'
            ))
            ->add('nome')
            ->add('sigla')
            ->add('ativo')

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UF::class,
        ]);
    }
}
