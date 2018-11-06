<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 24/07/2018
 * Time: 18:22
 */

namespace App\Form\DataTransformer;


use App\Entity\Main\Unidade;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UnidadeToStringTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (Unidade) to a string.
     *
     * @param  Unidade|null $unidade
     * @return string
     */
    public function transform($unidade)
    {
        if (null === $unidade) {
            return '';
        }

        return sprintf('[%s] %s',
            $unidade->getCodigo(),
            $unidade->getNome()
        );
    }

    /**
     * Transforms a string to an object (unidade).
     *
     * @param  string $unidade
     * @return Unidade|null
     * @throws TransformationFailedException if object (unidade) is not found.
     */
    public function reverseTransform($unidadeNome)
    {
        // no unidade? It's optional, so that's ok
        if (!$unidadeNome) {
            return;
        }

        $codigo = preg_replace('#^\[(.*?)\].*?$#', '$1', $unidadeNome);
        $unidade = $this->entityManager
            ->getRepository(Unidade::class)
            // query for the issue with this id
            ->findOneBy(['codigo' => $codigo])
        ;

        if (null === $unidade) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Não foi possível encontrar a unidade "%s"!',
                $unidadeNome
            ));
        }

        return $unidade;
    }
}
