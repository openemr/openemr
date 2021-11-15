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
 * This rule is for validating if a value being validated equals another value.
 *
 * @package Particle\Validator\Rule
 */
class Equal extends Rule
{
    /**
     * A constant that will be used when the value is not equal to the expected value.
     */
    const NOT_EQUAL = 'Equal::NOT_EQUAL';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_EQUAL => '{{ name }} must be equal to "{{ testvalue }}"'
    ];

    /**
     * @var mixed
     */
    protected $value;

    /**
     * Construct the equal validator.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Validates if each character in $value is a decimal digit.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if ($this->value === $value) {
            return true;
        }
        return $this->error(self::NOT_EQUAL);
    }

    /**
     * Returns the parameters that may be used in a validation message.
     *
     * @return array
     */
    protected function getMessageParameters()
    {
        return array_merge(parent::getMessageParameters(), [
            'testvalue' => $this->value
        ]);
    }
}
