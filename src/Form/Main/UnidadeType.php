<?php

namespace App\Form\Main;

use App\Entity\Localidade\UF;
use App\Entity\Main\Unidade;
use App\Form\DataTransformer\UFtoStringTransformer;
use App\Validator\Constraints\CNPJ;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnidadeType extends AbstractType
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
            ->add('codigo', TextType::class, ['label' => 'fields.name.codigo'])
            ->add('nome')
            ->add('endereco', TextType::class, ['label' => 'fields.name.endereco'])
            ->add('bairro')
            ->add('cidade')
            ->add('uf', EntityType::class,
                array(
                    'class' => UF::class,
                    'label' => 'fields.name.uf',
                    'choice_label' => function ($uf) {
                        return $uf->getNome() . ' [' . $uf->getSigla() . ']';
                    },
                    'choice_value' => 'sigla',
                    'placeholder' => 'choice-field.placeholder',
                ))
            ->add('cep', TextType::class, ['label' => 'fields.name.cep', 'attr' => array('data-input-mask' => 'cep')])
            ->add('cnpj', TextType::class,
                [
                    'label' => 'fields.name.cnpj',
                    'attr' => array('data-input-mask' => 'cnpj'),
                    'constraints' => [new CNPJ()]
                ])
            ->add('ativo')
        ;
        $builder->get('uf')
            ->addModelTransformer($this->uftransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Unidade::class,
        ]);
    }
}
