<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Processor;

use Laminas\Config\Config;
use Laminas\Config\Exception;
use Laminas\Filter\FilterInterface as LaminasFilter;

class Filter implements ProcessorInterface
{
    /**
     * @var LaminasFilter
     */
    protected $filter;

    /**
     * Filter all config values using the supplied Laminas\Filter
     *
     * @param LaminasFilter $filter
     */
    public function __construct(LaminasFilter $filter)
    {
        $this->setFilter($filter);
    }

    /**
     * @param  LaminasFilter $filter
     * @return self
     */
    public function setFilter(LaminasFilter $filter)
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @return LaminasFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Process
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

        /**
         * Walk through config and replace values
         */
        foreach ($config as $key => $val) {
            if ($val instanceof Config) {
                $this->process($val);
            } else {
                $config->$key = $this->filter->filter($val);
            }
        }

        return $config;
    }

    /**
     * Process a single value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function processValue($value)
    {
        return $this->filter->filter($value);
    }
}
