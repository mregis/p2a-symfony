<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 24/07/2018
 * Time: 18:22
 */

namespace App\Form\DataTransformer;


use App\Entity\Main\Transportadora;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TransportadoraToStringTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (Transportadora) to a string.
     *
     * @param  Transportadora|null $unidade
     * @return string
     */
    public function transform($transportadora)
    {
        if (null === $transportadora) {
            return '';
        }

        return sprintf('[%s] %s',
            $transportadora->getCodigo(),
            $transportadora->getNome()
        );
    }

    /**
     * Transforms a string to an object (transportadora).
     *
     * @param  string $transportadora
     * @return Transportadora|null
     * @throws TransformationFailedException if object (transportadora) is not found.
     */
    public function reverseTransform($transportadoraNome)
    {
        // no transportadora? It's optional, so that's ok
        if (!$transportadoraNome) {
            return;
        }

        $codigo = preg_replace('#^\[(.*?)\].*?$#', '$1', $transportadoraNome);
        $transportadora = $this->entityManager
            ->getRepository(Transportadora::class)
            // query for the issue with this id
            ->findOneBy(['codigo' => $codigo])
        ;

        if (null === $transportadora) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Não foi possível encontrar a transportadora "%s"!',
                $transportadoraNome
            ));
        }

        return $transportadora;
    }
}
