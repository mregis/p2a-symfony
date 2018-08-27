<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 02/03/2018
 * Time: 19:35
 */

namespace App\Tests\Util;

use App\Util\StringUtils;
use PHPUnit\Framework\TestCase;

class StringUtilsTest extends TestCase
{

    private $basicstring;
    private $latinstring;
    private $utf8string;

    public function __construct()
    {
        // ASCII string
        $this->basicstring = 'aeioucAEIOUC';

        // To avoid troubles using command line in different locales
        // the string used to create different charset is a plain HTML entities
        // Using html_entity_decode to convert the
        // string &atilde;&eacute;&igrave;&ocirc;&uuml;&ccedil;&Atilde;&Eacute;&igrave;&Ocirc;&Uuml;&Ccedil;
        // into ãéìôüçÃÉìÔÜÇ in different charsets
        $html_chars = '&atilde;&eacute;&igrave;&ocirc;&uuml;&ccedil;&Atilde;&Eacute;&igrave;&Ocirc;&Uuml;&Ccedil;';
        $this->utf8string = html_entity_decode($html_chars, ENT_HTML5, 'UTF-8');
        $this->latinstring = html_entity_decode($html_chars, ENT_HTML5, 'ISO-8859-1');
        parent::__construct();
    }

    public function testToUtf8()
    {
        $this->assertEquals($this->basicstring, StringUtils::toUtf8($this->basicstring), 'Basic UTF8 String fail to convet to UTF8');
        $this->assertEquals($this->utf8string, StringUtils::toUtf8($this->latinstring), 'Latin1 String fail to convet to UTF8');
        // Using Phrases
        $utf8phrase = $this->utf8string . ' ' . $this->utf8string . ', [' . $this->utf8string . ']';
        $latinphrase = $this->latinstring . ' ' . $this->latinstring . ', [' . $this->latinstring . ']';
        $this->assertEquals($utf8phrase, StringUtils::toUtf8($latinphrase), 'Latin1 String Phrase fail to convet to UTF8');
        // Mixed content
        $mixedphrase = $this->utf8string . ' ' . $this->latinstring . ', [' . $this->utf8string . ']';
        $this->assertEquals($utf8phrase, StringUtils::toUtf8($mixedphrase), 'Mixed String Phrase fail to convet to UTF8');
    }

    public function testToLatin()
    {
        $this->assertEquals($this->basicstring, StringUtils::toLatin($this->basicstring), 'Basic Latin String fail to convet to Latin');
        $this->assertEquals($this->latinstring, StringUtils::toLatin($this->utf8string), 'UTF-8 String fail to convet to Latin');
        // Using Phrases
        $utf8phrase = $this->utf8string . ' ' . $this->utf8string . ', [' . $this->utf8string . ']';
        $latinphrase = $this->latinstring . ' ' . $this->latinstring . ', [' . $this->latinstring . ']';
        $this->assertEquals($latinphrase, StringUtils::toLatin($utf8phrase), 'Utf8 String Phrase fail to convet to Latin');
        // Mixed content
        $mixedphrase = $this->utf8string . ' ' . $this->latinstring . ', [' . $this->utf8string . ']';
        $this->assertEquals($latinphrase, StringUtils::toLatin($mixedphrase), 'Mixed String Phrase fail to convet to Latin');
    }

    public function testSlugfy()
    {
        // Basic String
        $str = StringUtils::slugify($this->basicstring);
        $this->assertEquals(strtolower($this->basicstring), $str, 'Latin1 String cannot be slugfied');

        // Latin String
        $str = StringUtils::slugify($this->latinstring);
        $this->assertEquals(strtolower($this->basicstring), $str, 'Latin1 String cannot be slugfied');

        // UTF-8 String
        $str = StringUtils::slugify($str);
        $this->assertEquals(strtolower($this->basicstring), $str, 'UTF8 String cannot be slugfied');

    }

}