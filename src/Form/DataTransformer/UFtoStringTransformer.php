<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 15/05/2018
 * Time: 19:17
 */

namespace App\Form\DataTransformer;

use App\Entity\Localidade\UF;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class UFtoStringTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms a string (sigla) to an object (UF).
     *
     * @param  string $sigla
     * @return UF|null
     * @throws TransformationFailedException if object (UF) is not found.
     */
    public function transform($sigla): ?UF
    {
        // no sigla? It's optional, so that's ok
        if (!$sigla) {
            return null;
        }

        $repo = $this->entityManager
            ->getRepository(UF::class);
        $uf = $repo
            // query for the issue with this id
            ->findBySigla($sigla)
        ;

        if (null === $uf) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An UF with sigla "%s" does not exist!',
                $sigla
            ));
        }

        return $uf;

    }

    /**
     * Transforms an object (UF) to a string (sigla).
     *
     * @param  UF|null $uf
     * @return string
     */
    public function reverseTransform($uf)
    {
        if (null === $uf) {
            return '';
        }

        return $uf->getSigla();
    }

}