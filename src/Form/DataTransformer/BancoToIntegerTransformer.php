<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 15/05/2018
 * Time: 19:17
 */

namespace App\Form\DataTransformer;

use App\Entity\Agencia\Banco;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


class BancoToIntegerTransformer implements DataTransformerInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Transforms a integer (codigo) to an object (Banco).
     *
     * @param  integer $codigo
     * @return Banco|null
     * @throws TransformationFailedException if object (Banco) is not found.
     */
    public function transform($codigo)
    {
        // no codigo? It's optional, so that's ok
        if (!$codigo) {
            return;
        }

        $repo = $this->entityManager
            ->getRepository(Banco::class);
        $banco = $repo
            // query for the issue with this id
            ->findOneByCodigo((string)$codigo)
        ;

        if (null === $banco) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'O Banco "%s" não existe!',
                $codigo
            ));
        }

        return $banco;

    }

    /**
     * Transforms an object (Banco) to a string (codigo).
     *
     * @param  Banco|null $banco
     * @return string
     */
    public function reverseTransform($banco)
    {
        if (null === $banco) {
            return '';
        }

        return (int)$banco->getCodigo();
    }

}