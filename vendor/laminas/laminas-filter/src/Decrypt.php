<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

/**
 * Decrypts a given string
 */
class Decrypt extends Encrypt
{
    /**
     * Defined by Laminas\Filter\Filter
     *
     * Decrypts the content $value with the defined settings
     *
     * @param  string $value Content to decrypt
     * @return string The decrypted content
     */
    public function filter($value)
    {
        if (! is_string($value)) {
            return $value;
        }

        return $this->adapter->decrypt($value);
    }
}
