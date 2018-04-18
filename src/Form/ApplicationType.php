<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 12/04/2018
 * Time: 18:41
 */

namespace App\Form;


use App\Entity\OptionAttribute;
use Symfony\Component\Form\AbstractType;
use App\Entity\Application;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type as Types;

class ApplicationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'label' => 'application.name'
            ))
            ->add('alias', TextType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'label' => 'application.alias',
            ))
            ->add('options', Types\CollectionType::class,  array(
                'entry_type' => OptionAttributeType::class,
                'entry_options' => array('label' => false),
                'allow_add' => true,
                'label' => 'application.options-name',
            ))
            ->add('isActive', ChoiceType::class, array(
                'label' => 'application.status',
                'choices' => array('active' => true, 'inactive' => false),
                'placeholder' => 'choice-field.placeholder',
            ))
            ->add('submit', SubmitType::class, array(
                'label' => 'save',
            ));

//        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder){
//            /* @var $application Application */
//            $application = $event->getData();
//            $form = $event->getForm();
//            $optionsForm = $builder->create('options', OptionAttributeType::class, array('auto_initialize' => false,
//                'label' => 'application.options-name', 'required' => false));
//            if ($application && $application->getOptions()) {
//                $options = $application->getOptions();
//                foreach ($options as $option) {
//                    /* @var $option OptionAttribute */
//                    $optionsForm->add($option->getName(), Types::class .'\\' . $option->getType(), array(
//                        'label' =>  $option->getName(),
//                        'data' => $option->getDefaultValue(),
//                        'required' => false,
//                    ));
//                }
//
//            }
//            $form->add($optionsForm->getForm())
//                ->add('submit', SubmitType::class, array(
//                'label' => 'save',
//            ));
//        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Application::class,
            ]);
    }
}