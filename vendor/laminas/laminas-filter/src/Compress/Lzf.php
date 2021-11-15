<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter\Compress;

use Laminas\Filter\Exception;

/**
 * Compression adapter for Lzf
 */
class Lzf implements CompressionAlgorithmInterface
{
    /**
     * Class constructor
     *
     * @param  null $options
     * @throws Exception\ExtensionNotLoadedException if lzf extension missing
     */
    public function __construct($options = null)
    {
        if (! extension_loaded('lzf')) {
            throw new Exception\ExtensionNotLoadedException('This filter needs the lzf extension');
        }
    }

    /**
     * Compresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException if error occurs during compression
     */
    public function compress($content)
    {
        $compressed = lzf_compress($content);
        if (! $compressed) {
            throw new Exception\RuntimeException('Error during compression');
        }

        return $compressed;
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     * @throws Exception\RuntimeException if error occurs during decompression
     */
    public function decompress($content)
    {
        $compressed = lzf_decompress($content);
        if (! $compressed) {
            throw new Exception\RuntimeException('Error during decompression');
        }

        return $compressed;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Lzf';
    }
}
