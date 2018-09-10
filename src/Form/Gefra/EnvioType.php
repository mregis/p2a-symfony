<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 30/07/2018
 * Time: 19:26
 */

namespace App\Form\Gefra;

use App\Entity\Gefra\Envio;
use App\Entity\Gefra\Operador;
use App\Entity\Gefra\TipoEnvioStatus;
use App\Entity\Gefra\Transportadora;
use App\Form\DataTransformer\JuncaoToStringTransformer;
use App\Form\Type\AutocompleteJuncaoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EnvioType extends AbstractType
{
    private $juncaoTransformer;

    public function __construct(JuncaoToStringTransformer $juncaoTransformer)
    {
        $this->juncaoTransformer = $juncaoTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $envio Envio */
        $envio = $builder->getData();
        $builder
            ->add('transportadora', EntityType::class, [
                'class' => Transportadora::class,
                'placeholder' => 'choice-field.placeholder',
                'attr' => ['readonly' => ($envio->getId() != null)]
            ])
            ->add('operador', EntityType::class, [
                'class' => Operador::class,
                'placeholder' => 'choice-field.placeholder',
                'attr' => ['readonly' => ($envio->getId() != null)]
            ])
            ->add('juncao', AutocompleteJuncaoType::class, [
                    'label' => 'gefra.labels.juncao',
                    'attr' => ['readonly' => ($envio->getId() != null)]
            ])

            ->add('cte', TextType::class, [
                'label' => 'fields.name.cte',
                'label_attr' => ['title'=>'gefra.labels.cte'],
                'required' => false,
                'empty_data' => '',

            ])
            ->add('dt_emissao_cte', DateType::class,
                [
                    'widget' => 'single_text',
                    'label' => 'gefra.labels.dt-emissao-cte',
                    'required' => false,
                ])
            ->add('dt_coleta', DateType::class,
                [
                    'widget' => 'single_text',
                    'label' => 'gefra.labels.dt-coleta',
                    'required' => false
                ])
            ->add('dt_varredura', DateType::class,
                [
                    'widget' => 'single_text',
                    'label' => 'gefra.labels.dt-varredura',
                    'required' => false
                ])
            ->add('grm', TextType::class, [
                'label' => 'fields.name.grm',
                'attr' => [
                    'data-input-mask' => 'int',
                    'readonly' => ($envio->getId() != null)
                ]
            ])
            ->add('valor', MoneyType::class,
                [
                    'label' => 'fields.name.valor',
                    'currency' => 'BRL',
                    'scale' => 2,
                    'rounding_mode' => NumberToLocalizedStringTransformer::ROUND_CEILING,
                    'grouping' => 3,
                ])
            ->add('qt_vol', IntegerType::class, [
                'label' => 'fields.name.qt_vol',
                'label' => 'fields.name.qt_vol',
            ])
            ->add('peso', NumberType::class,
                [
                    'scale' => 3,
                    'label' => 'fields.name.peso',
                    'rounding_mode' => NumberToLocalizedStringTransformer::ROUND_CEILING,
                ])
            ->add('solicitacao', TextType::class, ['label' => 'fields.name.solicitacao', 'required' => false])
            ->add('recebedor', TextType::class, ['label' => 'fields.name.recebedor', 'required' => false])
            ->add('doc_recebedor', TextType::class, ['label' => 'fields.name.doc_recebedor', 'required' => false])
            ->add('status', EntityType::class, [
                    'class' => TipoEnvioStatus::class,
                    'placeholder' => 'choice-field.placeholder',
                    ]
            )
            ->add('observacao', TextareaType::class, ['label' => 'fields.name.observacao', 'required' => false])
        ;
        $builder->get('juncao')
            ->addModelTransformer($this->juncaoTransformer);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Envio::class,
        ]);
    }
}