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
 * This rule will validate a value to be between min and max.
 *
 * @package Particle\Validator\Rule
 */
class Between extends Rule
{
    /**
     * A constant for an error message if the value is exceeding the max value.
     */
    const TOO_BIG = 'Between::TOO_BIG';

    /**
     * A constant for an error message if the value is below the min value.
     */
    const TOO_SMALL = 'Between::TOO_SMALL';

    /**
     * The message templates which can be returned by this validator.
     *
     * @var array
     */
    protected $messageTemplates = [
        self::TOO_BIG => '{{ name }} must be less than or equal to {{ max }}',
        self::TOO_SMALL => '{{ name }} must be greater than or equal to {{ min }}',
    ];

    /**
     * The lower boundary.
     *
     * @var int
     */
    protected $min;

    /**
     * The upper boundary.
     *
     * @var int
     */
    protected $max;

    /**
     * Construct the Between rule.
     *
     * @param int $min
     * @param int $max
     */
    public function __construct($min, $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * Checks whether or not $value is between min and max for this rule.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        return !$this->tooSmall($value, self::TOO_SMALL) && !$this->tooLarge($value, self::TOO_BIG);
    }

    /**
     * Returns whether or not the value is too small, and logs an error if it is.
     *
     * @param mixed $value
     * @param string $error
     * @return bool
     */
    protected function tooSmall($value, $error)
    {
        if ($value < $this->min) {
            $this->error($error);
            return true;
        }
        return false;
    }

    /**
     * Returns whether or not the value is too large, and logs an error if it is.
     *
     * @param mixed $value
     * @param string $error
     * @return bool
     */
    protected function tooLarge($value, $error)
    {
        if ($value > $this->max) {
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
            'max' => $this->max
        ]);
    }
}
