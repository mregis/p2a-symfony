<?php

namespace App\Form\Agencia;

use App\Entity\Agencia\Banco;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\RegexValidator;

class BancoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nome', TextType::class, ['label' => 'banco.labels.nome'])
            ->add('codigo',TextType::class,['label' => 'banco.labels.codigo',
                'attr' => array('class' => 'int')])
            ->add('cnpj', TextType::class, ['label' => 'banco.labels.cnpj',
                'required' => false,
                'attr' => array('class' => 'cnpj')])
            ->add('is_active', CheckboxType::class, ['label' => 'active', 'required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Banco::class,
        ]);
    }
}
