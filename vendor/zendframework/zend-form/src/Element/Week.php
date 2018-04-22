<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\Element;

use Zend\Validator\DateStep as DateStepValidator;
use Zend\Validator\Regex as RegexValidator;
use Zend\Validator\GreaterThan as GreaterThanValidator;
use Zend\Validator\LessThan as LessThanValidator;

class Week extends DateTime
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'week',
    ];

    /**
     * Retrieves a Date Validator configured for a Week Input type
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getDateValidator()
    {
        return new RegexValidator('/^[0-9]{4}\-W[0-9]{2}$/');
    }

    /**
     * Retrieves a DateStep Validator configured for a Week Input type
     *
     * @return \Zend\Validator\ValidatorInterface
     */
    protected function getStepValidator()
    {
        $stepValue = (isset($this->attributes['step']))
                     ? $this->attributes['step'] : 1; // Weeks

        $baseValue = (isset($this->attributes['min']))
                     ? $this->attributes['min'] : '1970-W01';

        return new DateStepValidator([
            'format'    => 'Y-\WW',
            'baseValue' => $baseValue,
            'step'      => new \DateInterval("P{$stepValue}W"),
        ]);
    }

    /**
     * @see https://bugs.php.net/bug.php?id=74511
     * @return array
     */
    protected function getValidators()
    {
        if ($this->validators) {
            return $this->validators;
        }
        $validators = [];
        $validators[] = $this->getDateValidator();
        if (isset($this->attributes['min'])) {
            $validators[] = new GreaterThanValidator([
                'min'       => $this->attributes['min'],
                'inclusive' => true,
            ]);
        }
        if (isset($this->attributes['max'])) {
            $validators[] = new LessThanValidator([
                'max'       => $this->attributes['max'],
                'inclusive' => true,
            ]);
        }
        if (! isset($this->attributes['step'])
            || 'any' !== $this->attributes['step']
        ) {
            $validators[] = $this->getStepValidator();
        }
        $this->validators = $validators;
        return $this->validators;
    }
}
