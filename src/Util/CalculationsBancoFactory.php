<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 10/07/2018
 * Time: 15:50
 */

namespace App\Util;

/**
 * Factory de classes geradoras de calculos especificos como
 *     dígito verificador de Agência, Conta, Nosso número, etc.
 *
 *
 * Class CalculationsBancoFactory
 * @package App\Util
 */

class CalculationsBancoFactory
{

    static public function get(string $code)
    {
        $instance = null;
        switch($code) {
            case '237':
                $instance = new CalculationsBancoBradesco();
                break;
        }

        return $instance;
    }
}