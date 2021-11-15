<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Processor;

use Laminas\Config\Config;
use Laminas\Config\Exception;
use Laminas\Stdlib\PriorityQueue;

class Queue extends PriorityQueue implements ProcessorInterface
{
    /**
     * Process the whole config structure with each parser in the queue.
     *
     * @param  Config $config
     * @return Config
     * @throws Exception\InvalidArgumentException
     */
    public function process(Config $config)
    {
        if ($config->isReadOnly()) {
            throw new Exception\InvalidArgumentException('Cannot process config because it is read-only');
        }

        foreach ($this as $parser) {
            /** @var $parser ProcessorInterface */
            $parser->process($config);
        }
    }

    /**
     * Process a single value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function processValue($value)
    {
        foreach ($this as $parser) {
            /** @var $parser ProcessorInterface */
            $value = $parser->processValue($value);
        }

        return $value;
    }
}
