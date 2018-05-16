<?php

namespace App\Form\Agencia;

use App\Entity\Agencia\Agencia;
use App\Entity\Agencia\Banco;
use App\Entity\Localidade\UF;
use App\Form\DataTransformer\UFtoStringTransformer;
use App\Form\Type\UFType;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgenciaType extends AbstractType
{
    private $uftransformer;

    public function __construct(UFtoStringTransformer $transformer)
    {
        $this->uftransformer = $transformer;
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
            ->add('codigo', TextType::class, array('attr' => array('class' => 'int')))
            ->add('dv', TextType::class, array('attr' => array('class' => 'int', 'maxlength' => 1)))
            ->add('cep', TextType::class, array('attr' => array('class' => 'cep', 'maxlength' => 9)))
            ->add('uf', EntityType::class,
                array(
                    'class' => UF::class,
                    'choice_label' => function ($uf) {
                        return $uf->getNome() . ' [' . $uf->getSigla() . ']';
                    },
                    'group_by' => function ($uf) {
                        return $uf->getRegiao()->getNome();
                    },
                    'choice_value' => 'sigla',
                    /*'choice_value' => function($uf) {
                        return ($uf ? $uf->getSigla() : '');
                    },
                    'data' => function(EntityManager $er) use ($builder) {
                        $sigla = $builder->getData()->getUf();
                        if (!$uf = $er->getRepository(UF::class)->findOneBy(array('sigla' => $sigla)) ) {
                            $uf = new UF();
                        }
                        var_dump($uf);
                        return $uf;
                    },*/
                    'placeholder' => 'choice-field.placeholder',
                ))
            ->add('cidade')
            ->add('logradouro')
            ->add('numeral')
            ->add('complemento')
            ->add('bairro')
            ->add('is_active', CheckboxType::class, ['label' => 'active', 'required' => false]);

        $builder->get('uf')
            ->addModelTransformer($this->uftransformer);
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Agencia::class,
        ]);
    }
}
