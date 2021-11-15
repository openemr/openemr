<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Reader;

interface ReaderInterface
{
    /**
     * Read from a file and create an array
     *
     * @param  string $filename
     * @return array
     */
    public function fromFile($filename);

    /**
     * Read from a string and create an array
     *
     * @param  string $string
     * @return array|bool
     */
    public function fromString($string);
}
