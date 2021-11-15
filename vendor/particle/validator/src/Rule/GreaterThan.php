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
 * This rule will validate a value to be greater than a value.
 *
 * @package Particle\Validator\Rule
 */
class GreaterThan extends Rule
{
    /**
     * A constant for an error message if the value is not greater than the min.
     */
    const NOT_GREATER_THAN = 'GreaterThan::NOT_GREATER_THAN';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_GREATER_THAN => '{{ name }} must be greater than {{ min }}',
    ];

    /**
     * The lower boundary.
     *
     * @var int
     */
    protected $min;

    /**
     * Construct the GreaterThan rule.
     *
     * @param int $min
     */
    public function __construct($min)
    {
        $this->min = $min;
    }

    /**
     * Checks whether or not $value is greater than the min for this rule.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return !$this->notGreaterThan($value, self::NOT_GREATER_THAN);
    }

    /**
     * Returns whether or not the value is greater than the min, and logs an error if it isn't.
     *
     * @param mixed $value
     * @param string $error
     * @return bool
     */
    protected function notGreaterThan($value, $error)
    {
        if ($value <= $this->min) {
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
            'min' => $this->min,
        ]);
    }
}
