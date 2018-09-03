<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 16/08/2018
 * Time: 18:01
 */

namespace App\Validator\Constraints;

use App\Entity\Localidade\Feriado;
use App\Form\Localidade\FeriadoType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TipoFeriadoValidator extends ConstraintValidator
{

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value != Feriado::TIPOFERIADO_NACIONAL) {
            /* @var $form FeriadoType */
            $form = $this->context->getObject()->getParent();
            $feriado = $form->getData();
            $uf = $feriado->getUf();
            $local = $feriado->getLocal();
            if (
                ($value == Feriado::TIPOFERIADO_ESTADUAL && $uf == '' && $tipolocal = 'UF') ||
                ($value == Feriado::TIPOFERIADO_MUNICIPAL && $local == '' && $tipolocal = 'Local')
            ) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ tipoferiado }}', $value)
                    ->setParameter('{{ tipolocal }}', $tipolocal)
                    ->addViolation();
            }
        }
    }
}