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
use App\Repository\Main\UnidadeRepository;
use App\Repository\Malote\MalhaRepository;
use App\Repository\Main\TransportadoraRepository;
use App\Util\StringUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoteiroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* @var $envio Roteiro */
        $roteiro = $builder->getData();

        $builder
            ->add('agencia', AutocompleteJuncaoType::class, [
                'label' => 'fields.name.agencia',
                'attr' => ['readonly' => ($roteiro->getId() != null)]
            ])
            ->add('unidade', EntityType::class, [
                'class' => Unidade::class,
                'query_builder' => function(UnidadeRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.nome', 'ASC')
                        ;

                },
                'placeholder' => 'choice-field.placeholder',
                'attr' => ['readonly' => ($roteiro->getId() != null)],
                'choice_value' => function ($unidade = null) {
                     if ($unidade instanceof Unidade) {
                         $val = $unidade->getNome();
                     } else {
                         $val = function(entityManager $em) use ($unidade) {
                             if ($u = $em->getRepository(Unidade::class)->findOneBy(
                                 ['nome_canonico' => StringUtils::slugify($unidade)]
                             )) {
                                 return $u->getNome();
                             }
                             return $unidade;
                         };
                         $val = $unidade;
                     }
                    return $val;
                },
            ])
            ->add('rota')
            ->add('transportadora', EntityType::class, [
                'class' => Transportadora::class,
                'placeholder' => 'choice-field.placeholder',

                'query_builder' => function(TransportadoraRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nome', 'ASC')
                        ;

                },
                'choice_value' => function ($transportadora = null) {
                    if ($transportadora instanceof Unidade) {
                        $val = $transportadora->getNome();
                    } else {
                        $val = function(entityManager $em) use ($transportadora) {
                            if ($t = $em->getRepository(Unidade::class)->findOneBy(
                                ['nome_canonico' => StringUtils::slugify($transportadora)]
                            )) {
                                return $t->getNome();
                            }
                            return $transportadora;
                        };
                        $val = $transportadora;
                    }
                    return $val;
                },
            ])

            ->add('frequencia', ChoiceType::class,
                    [
                        'placeholder' => 'choice-field.placeholder',
                        'choices' => [
                            'alternado' => 'alternado',
                            'diario' => 'diario',
                        ],
                    ]
                )
            ->add('lote')
            ->add('cd')
            ->add('ativo', null, ['label' => 'active',])
            ->add('malha', EntityType::class, [
                'class' => Malha::class,
                'placeholder' => 'choice-field.placeholder',
                'query_builder' => function(MalhaRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.nome', 'ASC')
                        ;

                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Roteiro::class,
        ]);
    }
}
