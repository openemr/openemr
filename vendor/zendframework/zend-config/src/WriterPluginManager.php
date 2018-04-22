<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;

class WriterPluginManager extends AbstractPluginManager
{
    protected $instanceOf = Writer\AbstractWriter::class;

    protected $aliases = [
        'ini'      => Writer\Ini::class,
        'Ini'      => Writer\Ini::class,
        'json'     => Writer\Json::class,
        'Json'     => Writer\Json::class,
        'php'      => Writer\PhpArray::class,
        'phparray' => Writer\PhpArray::class,
        'phpArray' => Writer\PhpArray::class,
        'PhpArray' => Writer\PhpArray::class,
        'yaml'     => Writer\Yaml::class,
        'Yaml'     => Writer\Yaml::class,
        'xml'      => Writer\Xml::class,
        'Xml'      => Writer\Xml::class,
    ];

    protected $factories = [
        Writer\Ini::class      => InvokableFactory::class,
        Writer\Json::class     => InvokableFactory::class,
        Writer\PhpArray::class => InvokableFactory::class,
        Writer\Yaml::class     => InvokableFactory::class,
        Writer\Xml::class      => InvokableFactory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'zendconfigwriterini'      => InvokableFactory::class,
        'zendconfigwriterjson'     => InvokableFactory::class,
        'zendconfigwriterphparray' => InvokableFactory::class,
        'zendconfigwriteryaml'     => InvokableFactory::class,
        'zendconfigwriterxml'      => InvokableFactory::class,
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
