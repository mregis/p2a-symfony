<?php

namespace App\Form\Gefra;

use App\Entity\Agencia\Banco;
use App\Entity\Gefra\Juncao;
use App\Entity\Localidade\UF;
use App\Form\DataTransformer\BancoToIntegerTransformer;
use App\Form\DataTransformer\UFtoStringTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JuncaoType extends AbstractType
{
    private $bancotransformer;
    private $uftransformer;

    public function __construct(BancoToIntegerTransformer $bancotransformer,
                                UFtoStringTransformer $uftransformer)
    {
        $this->bancotransformer = $bancotransformer;
        $this->uftransformer = $uftransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('banco', EntityType::class,
                array('class' => Banco::class,
                    'choice_label' => function ($banco) {
                        return $banco->getCodigo() . ' - ' . $banco->getNome();
                    },
                    'placeholder' => 'choice-field.placeholder'
                ))
            ->add('nome')
            ->add('codigo')
            ->add('uf', EntityType::class,
                array(
                    'class' => UF::class,
                    'choice_label' => function ($uf) {
                        return $uf->getNome() . ' [' . $uf->getSigla() . ']';
                    },
                    'choice_value' => 'sigla',
                    'placeholder' => 'choice-field.placeholder',
                ))
            ->add('cidade')
            ->add('is_active', CheckboxType::class, ['label' => 'active', 'required' => false]);

        $builder->get('banco')
            ->addModelTransformer($this->bancotransformer);
        $builder->get('uf')
            ->addModelTransformer($this->uftransformer);


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Juncao::class,
        ]);
    }
}
