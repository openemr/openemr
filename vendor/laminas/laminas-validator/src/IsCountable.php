<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

use Countable;

/**
 * Validate that a value is countable and the count meets expectations.
 *
 * The validator has five specific behaviors:
 *
 * - You can determine if a value is countable only
 * - You can test if the value is an exact count
 * - You can test if the value is greater than a minimum count value
 * - You can test if the value is greater than a maximum count value
 * - You can test if the value is between the minimum and maximum count values
 *
 * When creating the instance or calling `setOptions()`, if you specify a
 * "count" option, specifying either "min" or "max" leads to an inconsistent
 * state and, as such will raise an Exception\InvalidArgumentException.
 */
class IsCountable extends AbstractValidator
{
    const NOT_COUNTABLE = 'notCountable';
    const NOT_EQUALS    = 'notEquals';
    const GREATER_THAN  = 'greaterThan';
    const LESS_THAN     = 'lessThan';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_COUNTABLE => 'The input must be an array or an instance of \\Countable',
        self::NOT_EQUALS    => "The input count must equal '%count%'",
        self::GREATER_THAN  => "The input count must be less than '%max%', inclusively",
        self::LESS_THAN     => "The input count must be greater than '%min%', inclusively",
    ];

    /**
     * Additional variables available for validation failure messages
     *
     * @var array
     */
    protected $messageVariables = [
        'count' => ['options' => 'count'],
        'min'   => ['options' => 'min'],
        'max'   => ['options' => 'max'],
    ];

    /**
     * Options for the between validator
     *
     * @var array
     */
    protected $options = [
        'count' => null,
        'min'   => null,
        'max'   => null,
    ];

    public function setOptions($options = [])
    {
        foreach (['count', 'min', 'max'] as $option) {
            if (! is_array($options) || ! isset($options[$option])) {
                continue;
            }

            $method = sprintf('set%s', ucfirst($option));
            $this->$method($options[$option]);
            unset($options[$option]);
        }

        return parent::setOptions($options);
    }

    /**
     * Returns true if and only if $value is countable (and the count validates against optional values).
     *
     * @param  iterable $value
     * @return bool
     */
    public function isValid($value)
    {
        if (! (is_array($value) || $value instanceof Countable)) {
            $this->error(self::NOT_COUNTABLE);
            return false;
        }

        $count = count($value);

        if (is_numeric($this->getCount())) {
            if ($count != $this->getCount()) {
                $this->error(self::NOT_EQUALS);
                return false;
            }

            return true;
        }

        if (is_numeric($this->getMax()) && $count > $this->getMax()) {
            $this->error(self::GREATER_THAN);
            return false;
        }

        if (is_numeric($this->getMin()) && $count < $this->getMin()) {
            $this->error(self::LESS_THAN);
            return false;
        }

        return true;
    }

    /**
     * Returns the count option
     *
     * @return mixed
     */
    public function getCount()
    {
        return $this->options['count'];
    }

    /**
     * Returns the min option
     *
     * @return mixed
     */
    public function getMin()
    {
        return $this->options['min'];
    }

    /**
     * Returns the max option
     *
     * @return mixed
     */
    public function getMax()
    {
        return $this->options['max'];
    }

    /**
     * @param mixed $value
     * @return void
     * @throws Exception\InvalidArgumentException if either a min or max option
     *     was previously set.
     */
    private function setCount($value)
    {
        if (isset($this->options['min']) || isset($this->options['max'])) {
            throw new Exception\InvalidArgumentException(
                'Cannot set count; conflicts with either a min or max option previously set'
            );
        }
        $this->options['count'] = $value;
    }

    /**
     * @param mixed $value
     * @return void
     * @throws Exception\InvalidArgumentException if either a count or max option
     *     was previously set.
     */
    private function setMin($value)
    {
        if (isset($this->options['count'])) {
            throw new Exception\InvalidArgumentException(
                'Cannot set count; conflicts with either a count option previously set'
            );
        }
        $this->options['min'] = $value;
    }

    /**
     * @param mixed $value
     * @return void
     * @throws Exception\InvalidArgumentException if either a count or min option
     *     was previously set.
     */
    private function setMax($value)
    {
        if (isset($this->options['count'])) {
            throw new Exception\InvalidArgumentException(
                'Cannot set count; conflicts with either a count option previously set'
            );
        }
        $this->options['max'] = $value;
    }
}
