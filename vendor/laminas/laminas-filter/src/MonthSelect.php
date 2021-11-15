<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

class MonthSelect extends AbstractDateDropdown
{
    /**
     * Year-Month
     *
     * @var string
     */
    protected $format = '%2$s-%1$s';

    /**
     * @var int
     */
    protected $expectedInputs = 2;
}
