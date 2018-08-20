<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 16/08/2018
 * Time: 18:01
 */

namespace App\Validator\Constraints;


use Symfony\Component\Validator\Constraint;

class TipoFeriado extends Constraint
{
    public $message = 'invalid_tipoferiado_args';
}