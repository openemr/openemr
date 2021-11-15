<?php
/**
 * Particle.
 *
 * @link      http://github.com/particle-php for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Particle (http://particle-php.com)
 * @license   https://github.com/particle-php/validator/blob/master/LICENSE New BSD License
 */
namespace Particle\Validator\Rule;

use Particle\Validator\Rule;

/**
 * This rule is for validating if a string consists out of only digits.
 *
 * @package Particle\Validator\Rule
 */
class Digits extends Rule
{
    /**
     * A constant that will be used when the value contains things other than digits.
     */
    const NOT_DIGITS = 'Digits::NOT_DIGITS';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_DIGITS => '{{ name }} may only consist out of digits',
    ];

    /**
     * Validates if each character in $value is a decimal digit.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (ctype_digit((string) $value)) {
            return true;
        }
        return $this->error(self::NOT_DIGITS);
    }
}
