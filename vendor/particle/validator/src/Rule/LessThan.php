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
 * This rule will validate a value to be less than a value.
 *
 * @package Particle\Validator\Rule
 */
class LessThan extends Rule
{
    /**
     * A constant for an error message if the value is not less than the max.
     */
    const NOT_LESS_THAN = 'LessThan::NOT_LESS_THAN';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_LESS_THAN => '{{ name }} must be less than {{ max }}',
    ];

    /**
     * The upper boundary.
     *
     * @var int
     */
    protected $max;

    /**
     * Construct the LessThan rule.
     *
     * @param int $max
     */
    public function __construct($max)
    {
        $this->max = $max;
    }

    /**
     * Checks whether or not $value is less than the max for this rule.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return !$this->notLessThan($value, self::NOT_LESS_THAN);
    }

    /**
     * Returns whether or not the value is less than the max, and logs an error if it isn't.
     *
     * @param mixed $value
     * @param string $error
     * @return bool
     */
    protected function notLessThan($value, $error)
    {
        if ($value >= $this->max) {
            $this->error($error);
            return true;
        }
        return false;
    }

    /**
     * Returns the parameters that may be used in a validation message.
     *
     * @return array
     */
    protected function getMessageParameters()
    {
        return array_merge(parent::getMessageParameters(), [
            'max' => $this->max,
        ]);
    }
}
