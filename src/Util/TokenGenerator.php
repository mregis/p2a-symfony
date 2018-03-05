<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 01/03/2018
 * Time: 21:08
 */

namespace App\Util;


use Symfony\Component\Routing\Exception\InvalidParameterException;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @var int
     */
    private $maxLength;

    /**
     * @var int
     */
    private $minLength;

    /**
     * @var bool
     */
    private $hasLetter;

    /**
     * @var bool
     */
    private $hasNumber;

    /**
     * @var bool
     */
    private $hasCapitalLetter;

    /**
     * @var string
     */
    private $domain;

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     * @return TokenGenerator
     */
    public function setDomain($domain = null)
    {
        ($domain != null && strlen($domain) < $this->minLength) && $domain = null;
        if ($domain == null) {
            ($this->hasNumber) && $domain .= '0123456789';
            ($this->hasLetter) && $domain .= implode("", range('a', 'z'));
            ($this->hasCapitalLetter) && $domain .= implode("", range('A', 'Z'));
            $this->hasSpecial && $domain .= '][{}%$#@!&()';
        }
        $this->domain = $domain;
        return $this;
    }

    /**
     * @var bool
     */
    private $hasSpecial;

    /**
     * @param int $minLength
     * @param int $maxLength
     * @param bool|false $hasNumber
     * @param bool|false $hasSpecial
     */
    public function __construct($minLength, $maxLength, $hasNumber = false, $hasSpecial = false)
    {
        if (! is_numeric($minLength) && is_numeric($maxLength)) {
            throw new InvalidParameterException('One or more arguments are invalid.');
        }
        if ($minLength < 5) {
            throw new InvalidParameterException(sprintf('Token length cannot be less then %d ', $minLength));
        }

        $this->minLength = min((int) $minLength, (int) $maxLength);
        $this->maxLength = max((int) $minLength, (int) $maxLength);
        $this->hasNumber = (bool) $hasNumber;
        $this->hasSpecial = (bool) $hasSpecial;
        $this->hasLetter = true;
        $this->hasCapitalLetter = false;
        $this->setDomain();
    }

    /**
     * {@inheritdoc}
     */
    public function generateToken()
    {
        $code = '';
        $length = rand($this->minLength, $this->maxLength);
        while (strlen($code) < $length ) {
            $rnd = rand(0, strlen($this->domain)-1);
            $code .= $this->domain{$rnd};
        }
        // Force Token to have at least one Capital Letter
        while ($this->hasCapitalLetter && !preg_match('#[A-Z]#', $code)) {
            $code = $this->generateToken();
        }
        return $code;
    }


    /**
     * Set class object to generate only Letters tokens
     * @return $this
     */
    public function setOnlyLetterOn()
    {
        $this->hasNumber = false;
        $this->hasLetter = true;
        $this->hasSpecial = false;
        $this->setDomain();
        return $this;
    }

    /**
     * Set class object to generate only Numbers tokens
     * @return $this
     */
    public function setOnlyNumberOn()
    {
        $this->hasNumber = true;
        $this->hasLetter = false;
        $this->hasCapitalLetter = false;
        $this->hasSpecial = false;
        $this->setDomain();
        return $this;
    }

    /**
     * Set class object to generate tokens with Capital Letters
     */
    public function setCapitalLetterOn()
    {
        $this->hasCapitalLetter = true;
        $this->setDomain();
        return $this;
    }

    /**
     * Set all arguments to default values
     * @return $this
     */
    public function reset()
    {
        $this->__construct($this->minLength, $this->maxLength);
        return $this;
    }
}