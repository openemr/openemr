<?php

namespace Adldap\Configuration\Validators;

use Adldap\Configuration\ConfigurationException;

class StringValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        if(!is_string($this->value)) {
            throw new ConfigurationException("Option {$this->key} must be a string.");
        }

        return true;
    }
}
