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

class DateTimeLocal extends DateTime
{
    const DATETIME_LOCAL_FORMAT = 'Y-m-d\TH:i';

    const DATETIME_FORMAT = self::DATETIME_LOCAL_FORMAT;

    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'datetime-local',
    ];

    /**
     * {@inheritDoc}
     */
    protected $format = self::DATETIME_LOCAL_FORMAT;

    /**
     * Retrieves a DateStepValidator configured for a Date Input type
     *
     * @return ValidatorInterface
     */
    protected function getStepValidator()
    {
        $stepValue = isset($this->attributes['step']) ? $this->attributes['step'] : 1; // Minutes

        $baseValue = isset($this->attributes['min']) ? $this->attributes['min'] : '1970-01-01T00:00';

        return new DateStepValidator([
            'format'    => $this->format,
            'baseValue' => $baseValue,
            'step'      => new DateInterval("PT{$stepValue}M"),
        ]);
    }
}
