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
 * This rule is for validating if a value is in an array.
 *
 * @package Particle\Validator\Rule
 */
class InArray extends Rule
{
    /**
     * A constant that will be used when the value is not in the array without strict checking.
     */
    const NOT_IN_ARRAY = 'InArray::NOT_IN_ARRAY';

    /**
     * Constant that set strict to true
     */
    const STRICT = true;

    /**
     * Constant that set strict to false
     */
    const NOT_STRICT = false;

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_IN_ARRAY => '{{ name }} must be in the defined set of values',
    ];

    /**
     * The array that contains the values to check.
     *
     * @var array
     */
    protected $array = [];

    /**
     * A bool denoting whether or not strict checking should be done.
     *
     * @var bool
     */
    protected $strict;

    /**
     * Construct the InArray rule.
     *
     * @param array $array
     * @param bool $strict
     */
    public function __construct(array $array, $strict = self::STRICT)
    {
        $this->array = $array;
        $this->strict = $strict;
    }

    /**
     * Validates if $value is in the predefined array.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (in_array($value, $this->array, $this->strict)) {
            return true;
        }
        return $this->error(self::NOT_IN_ARRAY);
    }

    /**
     * Returns the parameters that may be used in a validation message.
     *
     * @return array
     */
    protected function getMessageParameters()
    {
        $quote = function ($value) {
            return '"' . $value . '"';
        };

        return array_merge(parent::getMessageParameters(), [
            'values' => implode(', ', array_map($quote, $this->array))
        ]);
    }
}
