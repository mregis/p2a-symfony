<?php
/**
 * Created by PhpStorm.
 * User: Marcos Regis
 * Date: 13/04/2018
 * Time: 16:28
 */

namespace App\Entity;


class OptionAttribute implements \Serializable
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
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
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultvalue;
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
     * @return FormTypeInterface
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