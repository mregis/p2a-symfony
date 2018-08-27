<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 21/08/2018
 * Time: 19:38
 */

namespace App\Util;

/**
 * Class StringUtils - Functions to use around system eventually
 * @package App\Util
 */
class StringUtils
{
    /**
     * Format a numeric string to ###.###.###.####-##
     * @param string $cnpj
     */
    public static function maskCnpj(string $cnpj)
    {
        $cnpj = preg_replace("#\D#", "", $cnpj);
        if (preg_match("#(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})#", $cnpj, $matches)) {
            $cnpj = sprintf("%s.%s.%s.%s-%s", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
        }
        return $cnpj;
    }

    /**
     * Format a numeric string to ###.###.###-##
     * @param string $cpf
     */
    public static function maskCpf(string $cpf)
    {
        $cpf = preg_replace("#\D#", "", $cpf);
        if (preg_match("#(\d{3})(\d{3})(\d{3})(\d{2})#", $cpf, $matches)) {
            $cpf = sprintf("%s.%s.%s-%s", $matches[1], $matches[2], $matches[3], $matches[4]);
        }
        return $cpf;
    }

    /**
     * @param $str Convert string to lowercase and replace special chars to equivalents ou remove its
     * @return string
     */
    public static function slugify(string $string): string
    {
        $str = $string; // for comparisons
        $str = self::toUtf8($str); // Force to work with string in Latin
        $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);

        if ($str != htmlentities($string, ENT_QUOTES, 'UTF-8')) { // iconv fails
            $str = self::toUtf8($string);
            $str = htmlentities($str, ENT_QUOTES, 'UTF-8');
            $str = preg_replace('#&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);#i', '$1', $str);
            // Need to strip non ASCII chars or any other than a-z, A-Z, 0-9...
            $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
            $str = preg_replace(array('#[^0-9a-z]#i', '#[ -]+#'), ' ', $str);
            $str = trim($str, ' -');
        }

        // lowercase
        $string = strtolower($str);

        return $string;
    }

    /**
     * @param $str string String in any encoding
     * @return string
     */
    public static function toUtf8(string $str_in)
    {
        if (!function_exists('mb_detect_encoding')) {
            throw new \Exception('The Multi Byte String extension is absent!');
        }
        $str_out = [];
        $words = explode(" ", $str_in);
        foreach ($words as $word) {
            $current_encoding = mb_detect_encoding($word, 'UTF-8, ASCII, ISO-8859-1');
            $str_out[] = mb_convert_encoding($word, 'UTF-8', $current_encoding);
        }
        return implode(" ", $str_out);
    }

    /**
     * @param $str string
     * @return string
     */
    public static function toLatin(string $str_in)
    {
        if (!function_exists('mb_detect_encoding')) {
            throw new \Exception('The Multi Byte String extension is absent!');
        }

        $str_out = [];
        $words = explode(" ", $str_in);
        foreach ($words as $word) {
            $current_encoding = mb_detect_encoding($word, 'UTF-8, ASCII, ISO-8859-1');
            $str_out[] = mb_convert_encoding($word, 'ISO-8859-1', $current_encoding);
        }
        return implode(" ", $str_out);
    }
}