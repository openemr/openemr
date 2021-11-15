<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

class Dir extends AbstractFilter
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * Returns dirname($value)
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }
        $value = (string) $value;

        return dirname($value);
    }
}
