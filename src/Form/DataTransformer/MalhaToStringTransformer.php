<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 24/07/2018
 * Time: 18:22
 */

namespace App\Form\DataTransformer;


use App\Entity\Malote\Malha;
use App\Util\StringUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class MalhaToStringTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (Malha) to a string.
     *
     * @param  Malha|null $malha
     * @return string
     */
    public function transform($malha)
    {
        if (null === $malha) {
            return '';
        }

        return sprintf('%s',         
            $malha->getNome()
        );
    }

    /**
     * Transforms a string to an object (malha).
     *
     * @param  string $malha
     * @return Malha|null
     * @throws TransformationFailedException if object (malha) is not found.
     */
    public function reverseTransform($malhaNome)
    {
        // no malha? It's optional, so that's ok
        if (!$malhaNome) {
            return;
        }

        $malhaNomeCanonico = StringUtils::slugify($malhaNome);
        $malha = $this->entityManager
            ->getRepository(Malha::class)
            // query for the issue with this id
            ->findOneBy(['nome_canonico' => $malhaNomeCanonico])
        ;

        if (null === $malha) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Não foi possível encontrar a malha "%s"!',
                $malhaNome
            ));
        }

        return $malha;
    }
}
