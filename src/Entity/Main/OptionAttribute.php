<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 13/04/2018
 * Time: 16:28
 */

namespace App\Entity\Main;


class OptionAttribute implements \Serializable
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed
     */
    private $defaultvalue;

    /**
     * @var string
     */
    private $type;

    /**
     * @var boolean
     */
    private $required = false;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return OptionAttribute
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultvalue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        switch ($this->type) {
            case 'DateType':
                $ret = is_object($this->defaultvalue)? $this->defaultvalue->format('d-m-Y') : $this->defaultvalue;
                break;
            case 'NumberType':
            case 'MoneyType':
                $ret = number_format((float)$this->defaultvalue);
                break;
            default:
                $ret = $this->defaultvalue;
        }

        return (string) $ret;
    }

    /**
     * @param string $value
     * @return OptionAttribute
     */
    public function setDefaultValue($value)
    {
        $this->defaultvalue = $value;
        return $this;
    }

    /**
     * @return string FormTypeInterface
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return OptionAttribute
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * @param boolean $required
     * @return OptionAttribute
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->name,
            $this->defaultvalue,
            $this->type,
            $this->required,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->name,
            $this->defaultvalue,
            $this->type,
            $this->required,
            ) = unserialize($serialized);
    }
}