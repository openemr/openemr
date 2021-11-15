<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Writer;

interface WriterInterface
{
    /**
     * Write a config object to a file.
     *
     * @param  string  $filename
     * @param  mixed   $config
     * @param  bool $exclusiveLock
     * @return void
     */
    public function toFile($filename, $config, $exclusiveLock = true);

    /**
     * Write a config object to a string.
     *
     * @param  mixed $config
     * @return string
     */
    public function toString($config);
}
