<?php

namespace Adldap\Configuration\Validators;

use Adldap\Configuration\ConfigurationException;

class ArrayValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        if (!is_array($this->value)) {
            throw new ConfigurationException("Option {$this->key} must be an array.");
        }

        return true;
    }
}
