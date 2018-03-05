<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 02/03/2018
 * Time: 19:35
 */

namespace App\Tests\Util;

use App\Util\TokenGenerator;
use PHPUnit\Framework\TestCase;

class TokenGeneratorTest extends TestCase
{
    private $minlength = 5;
    private $maxlength = 10;

    /**
     * Test for TokenGenerator class constructor
     */
    public function testConstructor()
    {
        $tokengenerator = new TokenGenerator($this->minlength, $this->maxlength);
        $this->assertInstanceOf(TokenGenerator::class, $tokengenerator, 'TokenGenerator constructor fails');
    }

    ## Token Size Tests - BEGIN
    /**
     * Test for token creation  - must have at least <var>$minlength</var> length
     */
    public function testGenerateToken()
    {
        $tokengenerator = new TokenGenerator($this->maxlength, $this->maxlength);
        $token = $tokengenerator->generateToken();
        $this->assertGreaterThanOrEqual($this->minlength, strlen($token), 'Generated token does not have correct size');
    }

    public function testGenerateTokenWithInvertedArguments()
    {
        // inverted arguments
        $tokengenerator = new TokenGenerator($this->maxlength, $this->minlength);
        $token = $tokengenerator->generateToken();
        $this->assertGreaterThanOrEqual($this->minlength, strlen($token), 'Generated token does not have correct size');
    }

    public function testGenerateTokenWithFixedSize()
    {
        // inverted arguments
        $tokengenerator = new TokenGenerator($this->maxlength, $this->maxlength);
        $token = $tokengenerator->generateToken();
        $this->assertEquals($this->maxlength, strlen($token), 'Generated token does not have correct size');
    }
    ## Token Size Tests - END

    ## Token Contents Tests - BEGIN
    public function testGenerateTokenOnlyNumbers()
    {
        $tokengenerator = new TokenGenerator($this->minlength, $this->maxlength);
        $token = $tokengenerator->setOnlyNumberOn()->generateToken();
        $this->assertContainsOnly('numeric', [$token], false, 'Generated token must have only numbers when using OnlyNumerOn method.');
    }

    /**
     * Only Letter test Token Generate
     */
    public function testGenerateTokenOnlyLetter()
    {
        $tokengenerator = new TokenGenerator($this->minlength, $this->maxlength);
        $token = $tokengenerator->setOnlyLetterOn()->generateToken();
        $this->assertRegExp('#^[^\d\W]+$#', $token, 'Generated token must have only letters when using OnlyLetterOn method.');
    }

    /**
     * Test if generated Token has capitalized and non capitalized chars
     */
    public function testGenerateTokenCapitalLetterOn()
    {
        $tokengenerator = new TokenGenerator($this->minlength, $this->maxlength);
        $token = $tokengenerator->setCapitalLetterOn()->generateToken();
        $this->assertRegExp('#[A-Z]#', $token, 'Generated token must have only letters when using OnlyLetterOn method.');
    }

}