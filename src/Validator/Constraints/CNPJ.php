<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 09/05/2018
 * Time: 17:43
 */

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CNPJ extends Constraint
{
    public $message = '"{{ string }}" não é um CNPJ válido.';
}