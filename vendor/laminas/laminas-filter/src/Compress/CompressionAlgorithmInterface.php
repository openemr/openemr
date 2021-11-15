<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter\Compress;

/**
 * Compression interface
 */
interface CompressionAlgorithmInterface
{
    /**
     * Compresses $value with the defined settings
     *
     * @param  string $value Data to compress
     * @return string The compressed data
     */
    public function compress($value);

    /**
     * Decompresses $value with the defined settings
     *
     * @param  string $value Data to decompress
     * @return string The decompressed data
     */
    public function decompress($value);

    /**
     * Return the adapter name
     *
     * @return string
     */
    public function toString();
}
