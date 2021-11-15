<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Processor;

use Laminas\Config\Config;

interface ProcessorInterface
{
    /**
     * Process the whole Config structure and recursively parse all its values.
     *
     * @param  Config $value
     * @return Config
     */
    public function process(Config $value);

    /**
     * Process a single value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function processValue($value);
}
