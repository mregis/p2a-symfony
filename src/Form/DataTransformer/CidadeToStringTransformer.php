<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 24/07/2018
 * Time: 18:22
 */

namespace App\Form\DataTransformer;


use App\Entity\Localidade\Cidade;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CidadeToStringTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (Cidade) to a string.
     *
     * @param  Cidade|null $cidade
     * @return string
     */
    public function transform($cidade)
    {
        if (null === $cidade) {
            return '';
        }

        return sprintf('%s/%s',
            $cidade->getNome(),
            $cidade->getUf()->getSigla()
        );
    }

    /**
     * Transforms a string to an object (cidade).
     *
     * @param  string $cidadeNome
     * @return Cidade|null
     * @throws TransformationFailedException if object (cidade) is not found.
     */
    public function reverseTransform($cidadeNome)
    {
        // sem Cidade? It's optional, so that's ok
        if (!$cidadeNome) {
            return;
        }

        $cidade = null;
        if (preg_match("#^(.*?)/(.*?)$#", $cidadeNome, $matches)) {
            $cidade = $this->entityManager
                ->getRepository(Cidade::class)
                ->findByNomeAndUf($matches[1], $matches[2])
            ;
        }

        if (null === $cidade) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Não foi possível encontrar a cidade "%s"!',
                $cidadeNome
            ));
        }

        return $cidade;
    }
}
