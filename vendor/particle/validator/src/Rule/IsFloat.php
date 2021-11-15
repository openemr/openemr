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
 * This rule is for validating if a value represents a float.
 *
 * @package Particle\Validator\Rule
 */
class IsFloat extends Rule
{
    /**
     * A constant that will be used when the value does not represent a float.
     */
    const NOT_A_FLOAT = 'IsFloat::NOT_A_FLOAT';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_A_FLOAT => '{{ name }} must be a float',
    ];

    /**
     * Validates if $value represents a float.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (is_float($value)) {
            return true;
        }

        return $this->error(self::NOT_A_FLOAT);
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBreakChainOnError()
    {
        return true;
    }
}
