<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

class DateTimeSelect extends AbstractDateDropdown
{
    /**
     * Year-Month-Day Hour:Min:Sec
     *
     * @var string
     */
    protected $format = '%6$s-%4$s-%1$s %2$s:%3$s:%5$s';

    /**
     * @var int
     */
    protected $expectedInputs = 6;

    /**
     * @param mixed $value
     * @return array|mixed|null|string
     * @throws Exception\RuntimeException
     */
    public function filter($value)
    {
        if (! is_array($value)) {
            // nothing to do
            return $value;
        }

        if ($this->isNullOnEmpty()
            && (
                empty($value['year'])
                || empty($value['month'])
                || empty($value['day'])
                || empty($value['hour'])
                || empty($value['minute'])
                || (isset($value['second']) && empty($value['second']))
            )
        ) {
            return;
        }

        if ($this->isNullOnAllEmpty()
            && (
                empty($value['year'])
                && empty($value['month'])
                && empty($value['day'])
                && empty($value['hour'])
                && empty($value['minute'])
                && (! isset($value['second']) || empty($value['second']))
            )
        ) {
            // Cannot handle this value
            return;
        }

        if (! isset($value['second'])) {
            $value['second'] = '00';
        }

        $this->filterable($value);

        ksort($value);

        $value = vsprintf($this->format, $value);

        return $value;
    }
}
