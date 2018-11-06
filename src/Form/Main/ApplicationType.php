<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 12/04/2018
 * Time: 18:41
 */

namespace App\Form\Main;

use Symfony\Component\Form\AbstractType;
use App\Entity\Main\Application;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
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
            ->add('uri', TextType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'label' => 'application.uri',
            ))
            ->add('options', Types\CollectionType::class, array(
                'entry_type' => OptionAttributeType::class,
                'entry_options' => array('label' => false, 'isSubForm' => true,
                ),
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => 'application.options-name',
                'required' => false,
                'attr' => ['class' => 'subform-row container form-control-sm'],
            ))
            ->add('isActive', Types\CheckboxType::class, array(
                'label' => 'active',
                'required' => false,
            ));

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