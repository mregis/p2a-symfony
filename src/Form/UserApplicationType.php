<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 12/04/2018
 * Time: 18:41
 */

namespace App\Form;


use App\Entity\OptionAttribute;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use App\Entity\UserApplication;
use App\Entity\Application;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type as Types;

class UserApplicationType extends AbstractType
{
    /**
     * @var string
     */
    private $cancel_url;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->cancel_url = ($options['cancel_url'] == null ?
            'history.back();' :
            sprintf('javascript: window.location.href="%s";', $options['cancel_url'])
        );
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
            /* @var $userApplication UserApplication */
            $userApplication = $event->getData();
            $form = $event->getForm();
            /* @var $application Application */
            if ($application = $userApplication->getApplication()) {
                $form
                    ->add('application', EntityType::class, array(
                        'class' => Application::class,
                        'choice_label' => 'name',
                        'query_builder' => function (EntityRepository $er) use ($application) {
                            return $er->createQueryBuilder('a')
                                ->where('a.id = ?1')
                                ->setParameter('1', $application->getId());
                        },
                        'label' => 'user-application.list-app-available',
                        'expanded' => false,
                        'multiple' => false,
                        'required' => true,
                        'attr' => ['readonly' => true],
                    ));
                $form->add('options', Types\CollectionType::class, array(
                    'entry_type' => UserApplicationOptionAttributeType::class,
                    'entry_options' => array('label' => false,),
                    'allow_add' => true,
                    'by_reference' => false,
                    'allow_delete' => true,
                    'prototype' => false,
                    'label' => 'user-application.options-name',
                    'required' => false,
                    'attr' => ['class' => 'container form-control-sm'],
                ));
            } else {
                $form
                    ->add('application', EntityType::class, array(
                        'class' => Application::class,
                        'choice_label' => 'name',
                        'query_builder' => function (EntityRepository $er) use ($builder) {
                            $subq = $er->createQueryBuilder('sa')
                                ->innerJoin('\App\Entity\UserApplication', 'ua', 'WITH', 'ua.application = sa.id')
                                ->where('ua.user = ?1');
                            $qb = $er->createQueryBuilder('a');
                            return $qb->where($qb->expr()->notIn('a.id', $subq->getDQL()))
                                ->setParameter('1', $builder->getData()->getUser());
                        },
                        'label' => 'user-application.current-app',
                        'expanded' => false,
                        'multiple' => false,
                        'placeholder' => 'choice-field.placeholder',
                        'required' => true,
                    ));
            }

            $form->add('submit', Types\SubmitType::class, array(
                'label' => 'save',
                'attr' => array('class' => 'btn btn-inline  btn-primary'),

            ))
                ->add('cancel', Types\ButtonType::class, array(
                    'label' => 'cancel',
                    'attr' => array('class' => 'btn btn-inline', 'onclick' => $this->cancel_url),

                ));
        });

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => UserApplication::class,
                'cancel_url' => '',
            ]);
        $resolver->addAllowedTypes('cancel_url', 'string');
    }
}