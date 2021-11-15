<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config;

use Psr\Container\ContainerInterface;

use function array_keys;
use function array_values;
use function class_exists;
use function in_array;
use function sprintf;
use function strtolower;

class StandaloneWriterPluginManager implements ContainerInterface
{
    private $knownPlugins = [
        'ini'            => Writer\Ini::class,
        'javaproperties' => Writer\JavaProperties::class,
        'json'           => Writer\Json::class,
        'php'            => Writer\PhpArray::class,
        'phparray'       => Writer\PhpArray::class,
        'xml'            => Writer\Xml::class,
        'yaml'           => Writer\Yaml::class,
    ];

    /**
     * @param string $plugin
     * @return bool
     */
    public function has($plugin)
    {
        if (in_array($plugin, array_values($this->knownPlugins), true)) {
            return true;
        }

        return in_array(strtolower($plugin), array_keys($this->knownPlugins), true);
    }

    /**
     * @param string $plugin
     * @return Reader\ReaderInterface
     * @throws Exception\PluginNotFoundException
     */
    public function get($plugin)
    {
        if (! $this->has($plugin)) {
            throw new Exception\PluginNotFoundException(sprintf(
                'Config writer plugin by name %s not found',
                $plugin
            ));
        }

        if (! class_exists($plugin)) {
            $plugin = $this->knownPlugins[strtolower($plugin)];
        }

        return new $plugin();
    }
}
