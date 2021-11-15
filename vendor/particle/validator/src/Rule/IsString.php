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
 * This rule is for validating if a value represents a string.
 *
 * @package Particle\Validator\Rule
 */
class IsString extends Rule
{
    /**
     * A constant that will be used when the value does not represent a string.
     */
    const NOT_A_STRING = 'IsString::NOT_A_STRING';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_A_STRING => '{{ name }} must be a string',
    ];

    /**
     * Validates if $value represents a string.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (is_string($value)) {
            return true;
        }

        return $this->error(self::NOT_A_STRING);
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBreakChainOnError()
    {
        return true;
    }
}
