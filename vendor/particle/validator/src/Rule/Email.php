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
 * This rule is for validating if a value is a valid e-mail address.
 *
 * @package Particle\Validator\Rule
 */
class Email extends Rule
{
    /**
     * A constant that will be used when the value is not a valid e-mail address.
     */
    const INVALID_FORMAT = 'Email::INVALID_VALUE';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_FORMAT => '{{ name }} must be a valid email address',
    ];

    /**
     * Validates if the value is a valid email address.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
            return true;
        }
        return $this->error(self::INVALID_FORMAT);
    }
}
