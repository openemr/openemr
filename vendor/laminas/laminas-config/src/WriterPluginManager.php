<?php

/**
 * @see       https://github.com/laminas/laminas-config for the canonical source repository
 * @copyright https://github.com/laminas/laminas-config/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-config/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Config;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

use function array_merge_recursive;
use function get_class;
use function gettype;
use function is_object;
use function sprintf;

class WriterPluginManager extends AbstractPluginManager
{
    protected $instanceOf = Writer\AbstractWriter::class;

    protected $aliases = [
        'ini'            => Writer\Ini::class,
        'Ini'            => Writer\Ini::class,
        'json'           => Writer\Json::class,
        'Json'           => Writer\Json::class,
        'php'            => Writer\PhpArray::class,
        'phparray'       => Writer\PhpArray::class,
        'phpArray'       => Writer\PhpArray::class,
        'PhpArray'       => Writer\PhpArray::class,
        'yaml'           => Writer\Yaml::class,
        'Yaml'           => Writer\Yaml::class,
        'xml'            => Writer\Xml::class,
        'Xml'            => Writer\Xml::class,
        'javaproperties' => Writer\JavaProperties::class,
        'javaProperties' => Writer\JavaProperties::class,
        'JavaProperties' => Writer\JavaProperties::class,

        // Legacy Zend Framework aliases
        \Zend\Config\Writer\Ini::class => Writer\Ini::class,
        \Zend\Config\Writer\JavaProperties::class => Writer\JavaProperties::class,
        \Zend\Config\Writer\Json::class => Writer\Json::class,
        \Zend\Config\Writer\PhpArray::class => Writer\PhpArray::class,
        \Zend\Config\Writer\Yaml::class => Writer\Yaml::class,
        \Zend\Config\Writer\Xml::class => Writer\Xml::class,

        // v2 normalized FQCNs
        'zendconfigwriterini' => Writer\Ini::class,
        'zendconfigwriterjavaproperties' => Writer\JavaProperties::class,
        'zendconfigwriterjson' => Writer\Json::class,
        'zendconfigwriterphparray' => Writer\PhpArray::class,
        'zendconfigwriteryaml' => Writer\Yaml::class,
        'zendconfigwriterxml' => Writer\Xml::class,
    ];

    protected $factories = [
        Writer\Ini::class            => InvokableFactory::class,
        Writer\JavaProperties::class => InvokableFactory::class,
        Writer\Json::class           => InvokableFactory::class,
        Writer\PhpArray::class       => InvokableFactory::class,
        Writer\Yaml::class           => InvokableFactory::class,
        Writer\Xml::class            => InvokableFactory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'laminasconfigwriterini'            => InvokableFactory::class,
        'laminasconfigwriterjavaproperties' => InvokableFactory::class,
        'laminasconfigwriterjson'           => InvokableFactory::class,
        'laminasconfigwriterphparray'       => InvokableFactory::class,
        'laminasconfigwriteryaml'           => InvokableFactory::class,
        'laminasconfigwriterxml'            => InvokableFactory::class,
    ];

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against `$instanceOf`.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    public function validate($instance)
    {
        if (! $instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                '%s can only create instances of %s; %s is invalid',
                get_class($this),
                $this->instanceOf,
                (is_object($instance) ? get_class($instance) : gettype($instance))
            ));
        }
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $instance
     * @throws Exception\InvalidArgumentException
     */
    public function validatePlugin($instance)
    {
        try {
            $this->validate($instance);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function __construct(ContainerInterface $container, array $config = [])
    {
        $config = array_merge_recursive(['aliases' => $this->aliases], $config);
        parent::__construct($container, $config);
    }
}
