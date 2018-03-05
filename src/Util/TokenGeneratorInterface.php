<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 01/03/2018
 * Time: 21:07
 */

namespace App\Util;


interface TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generateToken();
}