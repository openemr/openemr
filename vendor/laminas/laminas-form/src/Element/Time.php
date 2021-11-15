<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use DateInterval;
use Laminas\Validator\DateStep as DateStepValidator;
use Laminas\Validator\ValidatorInterface;

use function date;

class Time extends DateTime
{
    const DATETIME_FORMAT = 'H:i:s';

    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'time',
    ];

    /**
     * Default date format
     * @var string
     */
    protected $format = 'H:i:s';

    /**
     * Retrieves a DateStepValidator configured for a Date Input type
     *
     * @return ValidatorInterface
     */
    protected function getStepValidator()
    {
        $format    = $this->getFormat();
        $stepValue = isset($this->attributes['step']) ? $this->attributes['step'] : 60; // Seconds

        $baseValue = isset($this->attributes['min']) ? $this->attributes['min'] : date($format, 0);

        return new DateStepValidator([
            'format'    => $format,
            'baseValue' => $baseValue,
            'step'      => new DateInterval("PT{$stepValue}S"),
        ]);
    }
}
