<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

use Laminas\Filter\Digits as DigitsFilter;

class Digits extends AbstractValidator
{
    const NOT_DIGITS   = 'notDigits';
    const STRING_EMPTY = 'digitsStringEmpty';
    const INVALID      = 'digitsInvalid';

    /**
     * Digits filter used for validation
     *
     * @var \Laminas\Filter\Digits
     */
    protected static $filter = null;

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_DIGITS   => 'The input must contain only digits',
        self::STRING_EMPTY => 'The input is an empty string',
        self::INVALID      => 'Invalid type given. String, integer or float expected',
    ];

    /**
     * Returns true if and only if $value only contains digit characters
     *
     * @param  string $value
     * @return bool
     */
    public function isValid($value)
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            $this->error(self::INVALID);
            return false;
        }

        $this->setValue((string) $value);

        if ('' === $this->getValue()) {
            $this->error(self::STRING_EMPTY);
            return false;
        }

        if (null === static::$filter) {
            static::$filter = new DigitsFilter();
        }

        if ($this->getValue() !== static::$filter->filter($this->getValue())) {
            $this->error(self::NOT_DIGITS);
            return false;
        }

        return true;
    }
}
