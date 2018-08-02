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
use App\Form\DataTransformer\JuncaoToStringTransformer;
use App\Form\Type\AutocompleteJuncaoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
        $builder
            ->add('operador', EntityType::class, [
                'class' => Operador::class,
                'placeholder' => 'choice-field.placeholder'])
            ->add('juncao', AutocompleteJuncaoType::class, ['label' => 'gefra.labels.juncao'])

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
                'attr' => ['data-input-mask' => 'int']
            ])
            ->add('valor', MoneyType::class,
                [
                    'label' => 'fields.name.valor',
                    'currency' => 'BRL',
                    'scale' => 2,
                    'rounding_mode' => NumberToLocalizedStringTransformer::ROUND_CEILING,
                    'attr' => ['data-input-mask' => 'moeda']
                ])
            ->add('qt_vol', IntegerType::class, ['label' => 'fields.name.qt_vol'])
            ->add('peso', NumberType::class,
                [
                    'scale' => 3,
                    'label' => 'fields.name.peso',
                    'rounding_mode' => NumberToLocalizedStringTransformer::ROUND_CEILING,
                    'attr' => ['data-input-mask' => 'peso']
                ])
            ->add('solicitacao', TextType::class, ['label' => 'fields.name.solicitacao'])
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