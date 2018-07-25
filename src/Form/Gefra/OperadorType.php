<?php

namespace App\Form\Gefra;

use App\Entity\Gefra\Operador;
use App\Entity\Localidade\UF;
use App\Form\DataTransformer\UFtoStringTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperadorType extends AbstractType
{
    /**
     * @var UFtoStringTransformer
     */
    private $uftransformer;

    public function __construct(UFtoStringTransformer $uftransformer)
    {
        $this->uftransformer = $uftransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nome')
            ->add('codigo')
            ->add('cidade')
            ->add('uf', EntityType::class,
                array(
                    'class' => UF::class,
                    'choice_label' => function ($uf) {
                        return $uf->getNome() . ' [' . $uf->getSigla() . ']';
                    },
                    'choice_value' => 'sigla',
                    'placeholder' => 'choice-field.placeholder',
                ))
            ->add('bairro')
            ->add('endereco')
            ->add('cep', TextType::class, ['label' => 'fields.name.cep', 'attr' => array('data-input-mask' => 'cep')])
            ->add('cnpj', TextType::class, ['label' => 'fields.name.cnpj', 'attr' => array('data-input-mask' => 'cnpj')])
            ->add('isActive', CheckboxType::class, ['label' => 'active', 'required' => false]);
        ;
        $builder->get('uf')
            ->addModelTransformer($this->uftransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Operador::class,
        ]);
    }
}
