<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

class ToFloat extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns (float) $value
     *
     * If the value provided is non-scalar, the value will remain unfiltered
     *
     * @param  mixed $value
     * @return float|mixed
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }

        return (float) $value;
    }
}
