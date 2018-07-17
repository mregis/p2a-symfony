<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class BulkRegistryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('registry', FileType::class,
            array(
                'constraints' => array(
                    new Assert\File(
                        array(
                            'maxSize' => '4096k',
                            'mimeTypes' => array('text/csv', 'application/vnd.ms-excel', 'text/plain')
                        )
                    )
                ),
                'label' => 'registry.file-field.label',
            )
        );
    }

}
