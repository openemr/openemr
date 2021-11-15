<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use DateInterval;
use DateTimeZone;
use Laminas\Form\Element\DateTime as DateTimeElement;
use Laminas\Validator\DateStep as DateStepValidator;
use Laminas\Validator\ValidatorInterface;

use function date;

class Date extends DateTimeElement
{
    const DATETIME_FORMAT = 'Y-m-d';

    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'date',
    ];

    /**
     * Date format to use for DateTime values. By default, this is RFC-3339,
     * full-date (Y-m-d), which is what HTML5 dictates.
     *
     * @var string
     */
    protected $format = 'Y-m-d';

    /**
     * Retrieves a DateStep Validator configured for a Date Input type
     *
     * @return ValidatorInterface
     */
    protected function getStepValidator()
    {
        $format    = $this->getFormat();
        $stepValue = isset($this->attributes['step']) ? $this->attributes['step'] : 1; // Days

        $baseValue = isset($this->attributes['min']) ? $this->attributes['min'] : date($format, 0);

        return new DateStepValidator([
            'format'    => $format,
            'baseValue' => $baseValue,
            'timezone'  => new DateTimeZone('UTC'),
            'step'      => new DateInterval("P{$stepValue}D"),
        ]);
    }
}
