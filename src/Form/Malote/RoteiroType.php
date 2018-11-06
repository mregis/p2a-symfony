<?php

namespace App\Form\Malote;

use App\Entity\Main\Transportadora;
use App\Entity\Main\Unidade;
use App\Entity\Malote\Malha;
use App\Entity\Malote\Roteiro;
use App\Form\DataTransformer\UnidadeToStringTransformer;
use App\Form\DataTransformer\JuncaoToStringTransformer;
use App\Form\DataTransformer\TransportadoraToStringTransformer;
use App\Form\Type\AutocompleteJuncaoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoteiroType extends AbstractType
{
    private $juncaoTransformer;

    private $unidadeTransformer;

    private $transportadoraTransformer;

    public function __construct(JuncaoToStringTransformer $juncaoTransformer,
                                UnidadeToStringTransformer $unidadeTransformer,
                                TransportadoraToStringTransformer $transportadoraTransformer
                                )
    {
        $this->juncaoTransformer = $juncaoTransformer;
        $this->unidadeTransformer = $unidadeTransformer;
        $this->transportadoraTransformer = $transportadoraTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $envio Roteiro */
        $roteiro = $builder->getData();

        $builder
            ->add('agencia', AutocompleteJuncaoType::class, [
                'label' => 'fields.name.agencia',
                'attr' => ['readonly' => ($roteiro->getId() != null)]
            ])
            ->add('rota')
            ->add('transportadora', EntityType::class, [
                'class' => Transportadora::class,
                'placeholder' => 'choice-field.placeholder',
                'attr' => ['readonly' => ($roteiro->getId() != null)]
            ])
            ->add('unidade', EntityType::class, [
                'class' => Unidade::class,
                'placeholder' => 'choice-field.placeholder',
                'attr' => ['readonly' => ($roteiro->getId() != null)]
            ])
            ->add('frequencia', ChoiceType::class,
                    [
                        'placeholder' => 'choice-field.placeholder',
                        'choices' => ['Alternado' => 'alternado', 'Diário' => 'diario'],
                    ]
                )
            ->add('lote')
            ->add('ativo')
            ->add('malha', EntityType::class, [
                'class' => Malha::class,
                'placeholder' => 'choice-field.placeholder',
            ])
        ;
        $builder->get('agencia')
            ->addModelTransformer($this->juncaoTransformer);
        $builder->get('transportadora')
            ->addModelTransformer($this->transportadoraTransformer);
        $builder->get('unidade')
            ->addModelTransformer($this->unidadeTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Roteiro::class,
        ]);
    }
}
