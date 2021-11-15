<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Writer;

use Laminas\Config\Exception;

use function json_encode;
use function json_last_error_msg;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class Json extends AbstractWriter
{
    /**
     * processConfig(): defined by AbstractWriter.
     *
     * @param  array $config
     * @return string
     * @throws Exception\RuntimeException if encoding errors occur.
     */
    public function processConfig(array $config)
    {
        $serialized = json_encode($config, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        if (false === $serialized) {
            throw new Exception\RuntimeException(json_last_error_msg());
        }

        return $serialized;
    }
}
