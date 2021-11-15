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
 * This rule is for validating if a value is a valid JSON string.
 *
 * @package Particle\Validator\Rule
 */
class Json extends Rule
{
    /**
     * A constant that will be used when the value is not a valid JSON string.
     */
    const INVALID_FORMAT = 'Json::INVALID_VALUE';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_FORMAT => '{{ name }} must be a valid JSON string',
    ];

    /**
     * Validates if the value is a valid JSON string.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (!is_string($value)) {
            return $this->error(self::INVALID_FORMAT);
        }

        json_decode($value);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->error(self::INVALID_FORMAT);
        }

        return true;
    }
}
