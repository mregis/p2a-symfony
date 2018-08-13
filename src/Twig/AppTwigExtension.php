<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 10/05/2018
 * Time: 15:24
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppTwigExtension extends AbstractExtension
{
    public function getFilters()
    {
        return array(
            new TwigFilter('cnpj', array($this, 'cnpjFilter')),
            new TwigFilter('cpf', array($this, 'cpfFilter')),
            new TwigFilter('cep', array($this, 'cepFilter')),
        );
    }

    public function cnpjFilter($cnpj)
    {
        $cnpj = preg_replace("#\D#", "", $cnpj);
        if (preg_match("#(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})#", $cnpj, $matches)) {
            $cnpj = sprintf("%s.%s.%s.%s-%s", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
        }
        return $cnpj;
    }

    public function cpfFilter($cpf)
    {
        $cpf = preg_replace("#\D#", "", $cpf);
        if (preg_match("#(\d{3})(\d{3})(\d{3})(\d{2})#", $cpf, $matches)) {
            $cpf = sprintf("%s.%s.%s-%s", $matches[1], $matches[2], $matches[3], $matches[4]);
        }
        return $cpf;
    }

    /**
     * @param $cep
     * @return mixed|string
     */
    public function cepFilter($cep)
    {
        $cep = preg_replace("#\D#", "", $cep);
        if (preg_match("#(\d{5})(\d{3})#", $cep, $matches)) {
            $cep = sprintf("%s-%s", $matches[1], $matches[2]);
        }
        return $cep;
    }
}