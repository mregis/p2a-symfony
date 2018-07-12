<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 10/07/2018
 * Time: 12:37
 */

namespace App\Util;



use Symfony\Component\Routing\Exception\InvalidParameterException;

class CalculationsBancoBradesco implements CalculationsBancoInterface
{
    /**
     * @param $seed
     * @return int
     */
    static function calculateDvAgencia($seed)
    {
        $seed = (int) $seed;
        if ($seed > 1 && $seed < 9999999) {
            $agencia = sprintf("%04d", $seed);
            $sum = 0;
            $multipliers = '5432';
            for ($i = 3; $i >= 0; $i--) {
                $sum += ($agencia{$i}) * ($multipliers{$i});
            }
            $mod11 = $sum % 11;
            $dv = (11 - $mod11) % 11;
            return ($dv < 10 ? $dv : 'P');
        } else {
            throw new InvalidParameterException(sprintf('%s não é um código de agência válido!', $seed));
        }
    }
}