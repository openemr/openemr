<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

class DateSelect extends AbstractDateDropdown
{
    /**
     * Year-Month-Day
     *
     * @var string
     */
    protected $format = '%3$s-%2$s-%1$s';

    /**
     * @var int
     */
    protected $expectedInputs = 3;
}
