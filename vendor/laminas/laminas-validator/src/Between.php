<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Validator;

use Laminas\Stdlib\ArrayUtils;
use Traversable;

class Between extends AbstractValidator
{
    const NOT_BETWEEN        = 'notBetween';
    const NOT_BETWEEN_STRICT = 'notBetweenStrict';
    const VALUE_NOT_NUMERIC  = 'valueNotNumeric';
    const VALUE_NOT_STRING   = 'valueNotString';

    /**
     * Retain if min and max are numeric values. Allow to not compare string and numeric types
     *
     * @var boolean
     */
    private $numeric;

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::NOT_BETWEEN        => "The input is not between '%min%' and '%max%', inclusively",
        self::NOT_BETWEEN_STRICT => "The input is not strictly between '%min%' and '%max%'",
        self::VALUE_NOT_NUMERIC  => "The min ('%min%') and max ('%max%') values are numeric, but the input is not",
        self::VALUE_NOT_STRING   => "The min ('%min%') and max ('%max%') values are non-numeric strings, "
            . 'but the input is not a string',
    ];

    /**
     * Additional variables available for validation failure messages
     *
     * @var array
     */
    protected $messageVariables = [
        'min' => ['options' => 'min'],
        'max' => ['options' => 'max'],
    ];

    /**
     * Options for the between validator
     *
     * @var array
     */
    protected $options = [
        'inclusive' => true,  // Whether to do inclusive comparisons, allowing equivalence to min and/or max
        'min'       => 0,
        'max'       => PHP_INT_MAX,
    ];

    /**
     * Sets validator options
     * Accepts the following option keys:
     *   'min' => scalar, minimum border
     *   'max' => scalar, maximum border
     *   'inclusive' => boolean, inclusive border values
     *
     * @param  array|Traversable $options
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($options = null)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (! is_array($options)) {
            $options = func_get_args();
            $temp['min'] = array_shift($options);
            if (! empty($options)) {
                $temp['max'] = array_shift($options);
            }

            if (! empty($options)) {
                $temp['inclusive'] = array_shift($options);
            }

            $options = $temp;
        }

        if (! array_key_exists('min', $options) || ! array_key_exists('max', $options)) {
            throw new Exception\InvalidArgumentException("Missing option: 'min' and 'max' have to be given");
        }

        if ((isset($options['min']) && is_numeric($options['min']))
            && (isset($options['max']) && is_numeric($options['max']))
        ) {
            $this->numeric = true;
        } elseif ((isset($options['min']) && is_string($options['min']))
            && (isset($options['max']) && is_string($options['max']))
        ) {
            $this->numeric = false;
        } else {
            throw new Exception\InvalidArgumentException(
                "Invalid options: 'min' and 'max' should be of the same scalar type"
            );
        }

        parent::__construct($options);
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
     * Sets the min option
     *
     * @param  mixed $min
     * @return $this Provides a fluent interface
     */
    public function setMin($min)
    {
        $this->options['min'] = $min;
        return $this;
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
     * Sets the max option
     *
     * @param  mixed $max
     * @return $this Provides a fluent interface
     */
    public function setMax($max)
    {
        $this->options['max'] = $max;
        return $this;
    }

    /**
     * Returns the inclusive option
     *
     * @return bool
     */
    public function getInclusive()
    {
        return $this->options['inclusive'];
    }

    /**
     * Sets the inclusive option
     *
     * @param  bool $inclusive
     * @return $this Provides a fluent interface
     */
    public function setInclusive($inclusive)
    {
        $this->options['inclusive'] = $inclusive;
        return $this;
    }

    /**
     * Returns true if and only if $value is between min and max options, inclusively
     * if inclusive option is true.
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if ($this->numeric && ! is_numeric($value)) {
            $this->error(self::VALUE_NOT_NUMERIC);
            return false;
        }
        if (! $this->numeric && ! is_string($value)) {
            $this->error(self::VALUE_NOT_STRING);
            return false;
        }

        if ($this->getInclusive()) {
            if ($this->getMin() > $value || $value > $this->getMax()) {
                $this->error(self::NOT_BETWEEN);
                return false;
            }
        } else {
            if ($this->getMin() >= $value || $value >= $this->getMax()) {
                $this->error(self::NOT_BETWEEN_STRICT);
                return false;
            }
        }

        return true;
    }
}
