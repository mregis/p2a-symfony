<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 24/07/2018
 * Time: 18:22
 */

namespace App\Form\DataTransformer;


use App\Entity\Gefra\Juncao;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class JuncaoToStringTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms an object (Juncao) to a string.
     *
     * @param  Juncao|null $juncao
     * @return string
     */
    public function transform($juncao)
    {
        if (null === $juncao) {
            return '';
        }

        return sprintf('[%s] %s - %s/%s',
            $juncao->getCodigo(),
            $juncao->getNome(),
            $juncao->getCidade(),
            $juncao->getUf()
        );
    }

    /**
     * Transforms a string to an object (juncao).
     *
     * @param  string $juncao
     * @return Juncao|null
     * @throws TransformationFailedException if object (juncao) is not found.
     */
    public function reverseTransform($juncaoNome)
    {
        // no juncao? It's optional, so that's ok
        if (!$juncaoNome) {
            return;
        }

        $codigo = preg_replace('#^\[(\d+)\].*?$#', '$1', $juncaoNome);
        $juncao = $this->entityManager
            ->getRepository(Juncao::class)
            // query for the issue with this id
            ->findOneBy(['codigo' => $codigo])
        ;

        if (null === $juncao) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'Não foi possível encontrar a junção "%s"!',
                $juncaoNome
            ));
        }

        return $juncao;
    }
}
