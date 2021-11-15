<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

class Hex extends AbstractValidator
{
    const INVALID = 'hexInvalid';
    const NOT_HEX = 'notHex';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID => 'Invalid type given. String expected',
        self::NOT_HEX => 'The input contains non-hexadecimal characters',
    ];

    /**
     * Returns true if and only if $value contains only hexadecimal digit characters
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (! is_string($value) && ! is_int($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue($value);
        if (! ctype_xdigit((string) $value)) {
            $this->error(self::NOT_HEX);
            return false;
        }

        return true;
    }
}
