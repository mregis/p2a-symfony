<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 10/05/2018
 * Time: 15:24
 */

namespace App\Twig;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppTwigExtension extends AbstractExtension
{

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new TwigFilter('cnpj', array($this, 'cnpjFilter')),
            new TwigFilter('cpf', array($this, 'cpfFilter')),
            new TwigFilter('cep', array($this, 'cepFilter')),
            new TwigFilter('localdate', array($this, 'localDate')),
        );
    }

    /**
     * @param $cnpj
     * @return mixed|string
     */
    public function cnpjFilter($cnpj)
    {
        $cnpj = preg_replace("#\D#", "", $cnpj);
        if (preg_match("#(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})#", $cnpj, $matches)) {
            $cnpj = sprintf("%s.%s.%s.%s-%s", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
        }
        return $cnpj;
    }

    /**
     * @param $cpf
     * @return mixed|string
     */
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

    /**
     * @param mixed $date
     * @param string $mask
     * @return string
     */
    public function localDate($date, $args)
    {
        $format = isset($args['format']) ? $args['format'] : '%d/%m/%Y [%a]';
        $locale = isset($args['locale']) ? $args['locale'] : '';

        if (preg_match("#^(.*?)\.(.*?)$#", $locale, $matches)) {
            $locale = $matches[1];
            $charset = $matches[2];
        } else {
            $charset = 'UTF-8'; // Fallback to locale.UTF-8
        }

        isset($args['charset']) && $charset = $args['charset'];

        $lctime_new_locale= $locale . '.' . $charset ;


        if ($date instanceof \DateTime) {
            /* @var $date \DateTime */
            $date = strtotime($date->format('Y-m-d'));
        } elseif(is_string($date)) {
            $date = strtotime(str_replace("/", "-", $date));
        }
        $lctime_locale = setlocale(LC_TIME, "0");
        setlocale(LC_TIME, $lctime_new_locale);
        $formatted_date = strftime($format, $date);
        setlocale(LC_TIME, $lctime_locale);
        return $formatted_date;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('checkroute', array($this, 'checkRoute')),
        );
    }

    /**
     * @param $value
     * @param $args
     */
    public function checkRoute($value)
    {
        try {
            $url =  $this->router->generate($value);
            if (null === $url) {
                return $value;
            }
        } catch (RouteNotFoundException $e) {
            return $value;
        }
        return $url;
    }
}