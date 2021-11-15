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
 * This rule is for validating if a value is a boolean value.
 *
 * @package Particle\Validator\Rule
 */
class Boolean extends Rule
{
    /**
     * A constant that will be used when the value is not in the array without strict checking.
     */
    const NOT_BOOL = 'BOOL::NOT_BOOL';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_BOOL => '{{ name }} must be either true or false',
    ];

    /**
     * Validates if $value is either true or false.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return is_bool($value) ?: $this->error(self::NOT_BOOL);
    }

    /**
     * {@inheritdoc}
     */
    public function shouldBreakChainOnError()
    {
        return true;
    }
}
