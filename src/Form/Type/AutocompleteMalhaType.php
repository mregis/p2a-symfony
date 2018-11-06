<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 24/07/2018
 * Time: 17:37
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutocompleteMalhaType extends AbstractType
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'attr' => ['autocomplete' => 'off']
        ));
    }

    public function getParent()
    {
        return SearchType::class;
    }
}
