<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 13/04/2018
 * Time: 16:36
 */

namespace App\Form\Main;

use App\Entity\Main\OptionAttribute;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type as Types;

class OptionAttributeType extends AbstractType
{

    /**
     * @var boolean
     */
    private $isSubForm;

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->isSubForm = $options['isSubForm'];

        $block_name = $this->isSubForm ? 'row_col' : null;
        $builder
            ->add('name', TextType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'label' => 'option-attribute.name',
                'required' => true,
                'block_name' => $block_name,
            ))
            ->add('type', ChoiceType::class, array(
                'constraints' => array(new Assert\NotBlank()),
                'label' => 'option-attribute.type',
                'required' => true,
                'block_name' => $block_name,
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
                'required' => false,
                'block_name' => $block_name,
            ))
            ->add('required', CheckboxType::class, array(
                'label' => 'option-attribute.required',
                'required' => false,
                'block_name' => $block_name,
            ))
            ->add('delete', Types\ButtonType::class, array(
                'label' => 'option-attribute.delete-btn',
                'attr' => array('class' => 'delete-option-btn' . ($this->isSubForm ? ' btn-sm btn-info fa fa-minus-circle' : '')),
            ))
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
                'isSubForm' => false,
            ]);
        $resolver->setAllowedTypes('isSubForm', 'boolean'); // Validates the type(s) of option(s) passed.
    }
}