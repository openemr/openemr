<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator\Barcode;

class Codabar extends AbstractAdapter
{
    /**
     * Constructor for this barcode adapter
     */
    public function __construct()
    {
        $this->setLength(-1);
        $this->setCharacters('0123456789-$:/.+ABCDTN*E');
        $this->useChecksum(false);
    }

    /**
     * Checks for allowed characters
     * @see Laminas\Validator\Barcode.AbstractAdapter::checkChars()
     */
    public function hasValidCharacters($value)
    {
        if (strpbrk($value, 'ABCD')) {
            $first = $value[0];
            if (! strpbrk($first, 'ABCD')) {
                // Missing start char
                return false;
            }

            $last = substr($value, -1, 1);
            if (! strpbrk($last, 'ABCD')) {
                // Missing stop char
                return false;
            }

            $value = substr($value, 1, -1);
        } elseif (strpbrk($value, 'TN*E')) {
            $first = $value[0];
            if (! strpbrk($first, 'TN*E')) {
                // Missing start char
                return false;
            }

            $last = substr($value, -1, 1);
            if (! strpbrk($last, 'TN*E')) {
                // Missing stop char
                return false;
            }

            $value = substr($value, 1, -1);
        }

        $chars  = $this->getCharacters();
        $this->setCharacters('0123456789-$:/.+');
        $result = parent::hasValidCharacters($value);
        $this->setCharacters($chars);
        return $result;
    }
}
