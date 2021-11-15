<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

/**
 * Decompresses a given string
 */
class Decompress extends Compress
{
    /**
     * Use filter as functor
     *
     * Decompresses the content $value with the defined settings
     *
     * @param  string $value Content to decompress
     * @return string The decompressed content
     */
    public function __invoke($value)
    {
        return $this->filter($value);
    }

    /**
     * Defined by FilterInterface
     *
     * Decompresses the content $value with the defined settings
     *
     * @param  string $value Content to decompress
     * @return string The decompressed content
     */
    public function filter($value)
    {
        if (! is_string($value) && $value !== null) {
            return $value;
        }

        return $this->getAdapter()->decompress($value);
    }
}
