<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 09/05/2018
 * Time: 17:45
 */

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CNPJValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($constraint->allowempty == true && $value == '') return;
        if (!$this->checkCNPJ($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }

    /**
     * checkCNPJ
     * Baseado em http://www.vivaolinux.com.br/script/Validacao-de-CPF-e-CNPJ/
     * Algoritmo em http://www.geradorcnpj.com/algoritmo_do_cnpj.htm
     * @param $cnpj string
     * @author Rafael Goulart <rafaelgou@rgou.net>
     * Retirado do plugin do SF1 brFormExtraPlugin
     */
    protected function checkCNPJ($cnpj)
    {
        $cnpj = preg_replace('#\D#', '', $cnpj);
        if (strlen($cnpj) <> 14) return false;

        // Primeiro dígito
        $multiplicadores = array(5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
        $soma = 0;
        for ($i = 0; $i <= 11; $i++) {
            $soma += $multiplicadores[$i] * $cnpj[$i];
        }

        $d1 = 11 - ($soma % 11);
        if ($d1 >= 10) $d1 = 0;

        // Segundo dígito
        $multiplicadores = array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2);
        $soma = 0;
        for ($i = 0; $i <= 12; $i++) {
            $soma += $multiplicadores[$i] * $cnpj[$i];
        }
        $d2 = 11 - ($soma % 11);
        if ($d2 >= 10) $d2 = 0;

        return ($cnpj[12] == $d1 && $cnpj[13] == $d2);
    }
}