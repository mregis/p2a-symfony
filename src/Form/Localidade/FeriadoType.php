<?php

namespace App\Form\Localidade;

use App\Entity\Localidade\Feriado;
use App\Entity\Localidade\UF;
use App\Form\DataTransformer\CidadeToStringTransformer;
use App\Form\Type\AutocompleteCidadeType;
use App\Validator\Constraints\TipoFeriado;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class FeriadoType extends AbstractType
{

    private $cidadeTransformer;

    public function __construct(CidadeToStringTransformer $cidadeTransformer)
    {
        $this->cidadeTransformer = $cidadeTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dt_feriado', DateType::class, [
                'widget' => 'single_text',
                'label' => 'localidade.feriado.labels.dt-feriado',
            ])
            ->add('uf', EntityType::class, array('class' => UF::class,
                'choice_label' => function ($uf) {
                    return $uf->getNome() . ' [' . $uf->getSigla() . ']';
                },
                'group_by' => function($uf) {
                    return $uf->getRegiao()->getNome();
                },
                'placeholder' => 'choice-field.placeholder',
                'required' => false,
                'multiple' => false,
            ))
            ->add('local', AutocompleteCidadeType::class, ['label' => 'fields.name.cidade', 'required' => false])
            ->add('tipo', ChoiceType::class, [
                    'placeholder' => 'choice-field.placeholder',
                    'label' => 'localidade.feriado.labels.tipo',
                    'constraints' => array(new TipoFeriado(), new NotBlank() ),
                    'choices' => [
                        'localidade.tipoferiado.labels.' . self::TIPOFERIADO_NACIONAL => self::TIPOFERIADO_NACIONAL,
                        'localidade.tipoferiado.labels.' . self::TIPOFERIADO_ESTADUAL => self::TIPOFERIADO_ESTADUAL,
                        'localidade.tipoferiado.labels.' . self::TIPOFERIADO_MUNICIPAL => self::TIPOFERIADO_MUNICIPAL,
                    ]
                ]
            )
            ->add('descricao', TextType::class, ['label' => 'fields.name.descricao', 'required' => false])
        ;
        $builder->get('local')->addModelTransformer($this->cidadeTransformer);

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Feriado::class,
        ]);
    }
}
