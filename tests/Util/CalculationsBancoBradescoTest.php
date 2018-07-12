<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 10/07/2018
 * Time: 13:17
 */

namespace App\Tests\Util;


use App\Util\CalculationsBancoBradesco;
use PHPUnit\Framework\TestCase;

class CalculationsBancoBradescoTest extends TestCase
{

    private $agenciasReais = array('3450' => '9', '7915' => '4', '108' => '2', '0497' => '9');

    ## DigitoVerificadorBradesco Tests - BEGIN

    /**
     * Test for calculate Mod11
     */
    public function testCalculateDVAgencia()
    {
        foreach ($this->agenciasReais as $agencia => $dv) {
            $this->assertEquals(
                CalculationsBancoBradesco::calculateDvAgencia($agencia),
                $dv,
                'Cálculo do Dígito Verificador Bradesco falhou.'
            );
        }
    }

}