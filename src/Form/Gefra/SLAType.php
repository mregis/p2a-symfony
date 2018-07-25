<?php

namespace App\Form\Gefra;

use App\Entity\Gefra\Operador;
use App\Entity\Gefra\SLA;
use App\Form\DataTransformer\JuncaoToStringTransformer;
use App\Form\Type\AutocompleteJuncaoType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SLAType extends AbstractType
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
            ->add('prazo')
        ;
        $builder->get('juncao')
            ->addModelTransformer($this->juncaoTransformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SLA::class,
        ]);
    }
}
