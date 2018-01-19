<?php

namespace Adldap\Configuration\Validators;

use Adldap\Configuration\ConfigurationException;

class IntegerValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        if (!is_int($this->value)) {
            throw new ConfigurationException("Option {$this->key} must be an integer.");
        }

        return true;
    }
}
