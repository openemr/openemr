<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config\Writer;

use Laminas\Config\Exception;

use function call_user_func;
use function function_exists;
use function is_callable;

class Yaml extends AbstractWriter
{
    /**
     * YAML encoder callback
     *
     * @var callable
     */
    protected $yamlEncoder;

    /**
     * Constructor
     *
     * @param callable|string|null $yamlEncoder
     */
    public function __construct($yamlEncoder = null)
    {
        if ($yamlEncoder !== null) {
            $this->setYamlEncoder($yamlEncoder);
        } else {
            if (function_exists('yaml_emit')) {
                $this->setYamlEncoder('yaml_emit');
            }
        }
    }

    /**
     * Get callback for decoding YAML
     *
     * @return callable
     */
    public function getYamlEncoder()
    {
        return $this->yamlEncoder;
    }

    /**
     * Set callback for decoding YAML
     *
     * @param callable $yamlEncoder the decoder to set
     * @return self
     * @throws Exception\InvalidArgumentException
     */
    public function setYamlEncoder($yamlEncoder)
    {
        if (! is_callable($yamlEncoder)) {
            throw new Exception\InvalidArgumentException('Invalid parameter to setYamlEncoder() - must be callable');
        }
        $this->yamlEncoder = $yamlEncoder;
        return $this;
    }

    /**
     * processConfig(): defined by AbstractWriter.
     *
     * @param  array $config
     * @return string
     * @throws Exception\RuntimeException
     */
    public function processConfig(array $config)
    {
        if (null === $this->getYamlEncoder()) {
            throw new Exception\RuntimeException("You didn't specify a Yaml callback encoder");
        }

        $config = call_user_func($this->getYamlEncoder(), $config);
        if (null === $config) {
            throw new Exception\RuntimeException("Error generating YAML data");
        }

        return $config;
    }
}
