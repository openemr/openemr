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
 * This rule is for validating if a value is an array.
 *
 * @package Particle\Validator\Rule
 */
class IsArray extends Rule
{
    /**
     * A constant that will be used when the value does not represent an integer value.
     */
    const NOT_AN_ARRAY = 'IsArray::NOT_AN_ARRAY';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_AN_ARRAY => '{{ name }} must be an array',
    ];

    /**
     * Validates if $value is an array.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (is_array($value)) {
            return true;
        }

        return $this->error(self::NOT_AN_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBreakChainOnError()
    {
        return true;
    }
}
