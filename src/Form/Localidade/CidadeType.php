<?php

namespace App\Form\Localidade;

use App\Entity\Localidade\Cidade;
use App\Entity\Localidade\UF;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CidadeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('uf', EntityType::class, array('class' => UF::class,
                'choice_label' => function ($uf) {
                    return $uf->getNome() . ' [' . $uf->getSigla() . ']';
                },
                'group_by' => function($uf) {
                    return $uf->getRegiao()->getNome();
                },
                'placeholder' => 'choice-field.placeholder'
            ))
            ->add('nome')
            ->add('abreviacao',
                    TextType::class,
                    ['label' => 'localidade.cidade.labels.abreviacao'])
            ->add('ativo', CheckboxType::class,
                ['label' => 'active'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cidade::class,
        ]);
    }
}
