<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 13/04/2018
 * Time: 16:36
 */

namespace App\Form;

use App\Entity\OptionAttribute;
use Symfony\Component\Form\AbstractType;
use App\Entity\Application;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type as Types;

class OptionAttributeType extends AbstractType
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
                'label' => 'option-attribute.name'
            ))
            ->add('type', ChoiceType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'label' => 'option-attribute.type',
                'choices' => array(
                    'ChoiceType',
                    'TextType',
                    'TextareaType',
                    'EmailType',
                    'IntegerType',
                    'MoneyType',
                    'NumberType',
                    'UrlType',
                    'TelType',
                    'DateType',
                    'DateTimeType',
                    'TimeType',
                    'PercentType',
                    'SearchType',
                    'ColorType',
                    'BirthdayType',
                    'CheckboxType',
                    'FileType',
                ),
                'choice_label' => function ($value, $key, $index) {
                    return 'option-attribute.labels.' . $value;
                },

            ))
            ->add('defaultvalue', TextType::class, array(
                'label' => 'option-attribute.defaultvalue',
                'required' => false
            ))
            ->add('required', CheckboxType::class, array(
                'label' => 'option-attribute.required',

            ))
//            ->add('submit', SubmitType::class, array('label' => 'add'))
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => OptionAttribute::class,
            ]);
    }
}