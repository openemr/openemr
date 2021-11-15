<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\Barcode;

class Upce extends AbstractAdapter
{
    /**
     * Constructor for this barcode adapter
     */
    public function __construct()
    {
        $this->setLength([6, 7, 8]);
        $this->setCharacters('0123456789');
        $this->setChecksum('gtin');
    }

    /**
     * Overrides parent checkLength
     *
     * @param string $value Value
     * @return bool
     */
    public function hasValidLength($value)
    {
        if (strlen($value) != 8) {
            $this->useChecksum(false);
        } else {
            $this->useChecksum(true);
        }

        return parent::hasValidLength($value);
    }
}
