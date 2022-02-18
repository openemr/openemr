<?php

/**
 * A POPO of the Config Model
 */

namespace OpenEMR\Core\Model;

class Config
{
    private $id;

    private $namespace;

    private $name;

    private $value;

    public function __construct(array $config)
    {
        $opts = [
            'id' => 'setId',
            'namespace' => 'setNamespace',
            'name' => 'setName',
            'value' => 'setValue',
        ];

        foreach ($config as $key => $value) {
            if (array_key_exists($key, $opts)) {
                $this->$opts[$key]($value);
            }
        }
    }

    public function getId()
    {
        return ($this->id ?? null);
    }

    public function getNamespace()
    {
        return ($this->namespace ?? null);
    }

    public function getName()
    {
        return ($this->name ?? null);
    }

    public function getValue()
    {
        return ($this->value ?? null);
    }

    public function hasId()
    {
        return ($this->id ?? false);
    }

    public function hasNamespace()
    {
        return ($this->namespace ?? false);
    }

    public function hasName()
    {
        return ($this->name ?? false);
    }

    public function hasValue()
    {
        return ($this->value ?? false);
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
