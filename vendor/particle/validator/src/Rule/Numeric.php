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
 * This rule is for validating if a value represents an numeric value (float, int).
 *
 * @package Particle\Validator\Rule
 */
class Numeric extends Rule
{
    /**
     * A constant that will be used when the value does not represent a numeric value.
     */
    const NOT_NUMERIC = 'Numeric::NOT_NUMERIC';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_NUMERIC => '{{ name }} must be numeric',
    ];

    /**
     * Validates if $value represents an integer or a float value.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (is_numeric($value)) {
            return true;
        }
        return $this->error(self::NOT_NUMERIC);
    }
}
