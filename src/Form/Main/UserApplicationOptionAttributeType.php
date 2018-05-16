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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type as Types;

class UserApplicationOptionAttributeType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
            /* @var $oaOption OptionAttribute */
            $oaOption = $event->getData();
            $form = $event->getForm();
            $form->add('name',
                Types\TextType::class, array(
                    'label' => 'name',
                    'data' => $oaOption->getName(),
                    'required' => true,
                    'attr' => ['readonly' => true]
                ))
                ->add('defaultvalue',
                    Types::class . '\\' . $oaOption->getType(),
//                    Types\TextType::class,
                    array(
                        'label' => 'value',
                        'data' => $oaOption->__toString(),
                        'required' => $oaOption->isRequired(),
                    ));
            if ($oaOption->isRequired()) {
                $form->add('delete', Types\ButtonType::class, array(
                    'label' => 'option-attribute.delete-btn',
                    'attr' => array('class' => 'delete-option-btn btn-sm btn-info fa fa-minus-circle'),
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
                'data_class' => OptionAttribute::class,
            ]);

    }
}