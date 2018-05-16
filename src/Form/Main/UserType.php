<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 05/04/2018
 * Time: 19:45
 */

namespace App\Form\Main;

use App\Entity\Main\Profile;
use App\Entity\Main\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type as Types;

class UserType extends AbstractType
{

    /**
     * @var User
     */
    private $userCaller;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->userCaller = $options['userCaller'];

        $builder
            ->add('name', TextType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'label' => 'users.name'
            ))
            ->add('email', EmailType::class, array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Email()),
                'label' => 'users.email',
            ))
            ->add('username', TextType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'label' => 'users.username',
            ))

            ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /* @var User */
            $user = $event->getData();
            $form = $event->getForm();
            if ($user && $user->getProfile() && $this->userCaller->getProfile() &&
                $this->userCaller->getProfile()->getId() == $user->getProfile()->getId()) {
                $form->add('profile', EntityType::class, array(
                    'class' => Profile::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->where('p.isActive = :status AND p.id = :id')
                            ->orderBy('p.level', 'DESC')
                            ->setParameters(['status' => true, 'id' => $this->userCaller->getProfile()->getId()]);
                    },
                    'attr' => ['readonly' => true, 'title' => $user->getProfile()->getDescription()],
                    'choice_label' => function ($er) {
                        return 'roles.names.' . $er->getName();
                    },

                    'label' => 'users.profile',
                    'choice_translation_domain' => null,

                ))
                    ->add('isActive', ChoiceType::class, array(
                        'label' => 'users.status',
                        'attr' => ['readonly' => true],
                        'choices' => array('active' => true),
                    ));
            } else {
                $form->add('profile', EntityType::class, array(
                    'class' => Profile::class,
                    'query_builder' => function (EntityRepository $er) use ($user) {
                        return $er->createQueryBuilder('p')
                            ->where('p.isActive = :status AND p.level > :level')
                            ->orderBy('p.level', 'DESC')
                            ->setParameters(['status' => true, 'level' => $this->userCaller->getProfile()->getLevel()]);
                    },
                    'choice_label' => function ($er) {
                        return 'roles.names.' . $er->getName();
                    },
                    'choice_attr' => function ($er) {
                        return ['title' => $er->getDescription()];
                    },
                    'label' => 'users.profile',
                    'choice_translation_domain' => null,
                    'placeholder' => 'choice-field.placeholder',

                ))
                    ->add('isActive', Types\CheckboxType::class, array(
                        'label' => 'active',
                    ));
            }
        });


    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => User::class,
                'userCaller' => new User(),
            ]);
        $resolver->setRequired('userCaller'); // Requires that currentOrg be set by the caller.
        $resolver->setAllowedTypes('userCaller', User::class); // Validates the type(s) of option(s) passed.

    }
}