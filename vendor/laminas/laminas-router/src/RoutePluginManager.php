<?php

/**
 * @see       https://github.com/laminas/laminas-router for the canonical source repository
 * @copyright https://github.com/laminas/laminas-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Router;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

/**
 * Plugin manager implementation for routes
 *
 * Enforces that routes retrieved are instances of RouteInterface. It overrides
 * configure() to map invokables to the component-specific
 * RouteInvokableFactory.
 *
 * The manager is marked to not share by default, in order to allow multiple
 * route instances of the same type.
 */
class RoutePluginManager extends AbstractPluginManager
{
    /**
     * Only RouteInterface instances are valid
     *
     * @var string
     */
    protected $instanceOf = RouteInterface::class;

    /**
     * Do not share instances. (v3)
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Do not share instances. (v2)
     *
     * @var bool
     */
    protected $sharedByDefault = false;

    /**
     * Constructor
     *
     * Ensure that the instance is seeded with the RouteInvokableFactory as an
     * abstract factory.
     *
     * @param ContainerInterface|\Laminas\ServiceManager\ConfigInterface $configOrContainerInstance
     * @param array $v3config
     */
    public function __construct($configOrContainerInstance, array $v3config = [])
    {
        $this->addAbstractFactory(RouteInvokableFactory::class);
        parent::__construct($configOrContainerInstance, $v3config);
    }

    /**
     * Validate a route plugin. (v2)
     *
     * @param object $plugin
     * @throws InvalidServiceException
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                RouteInterface::class
            ));
        }
    }

    /**
     * Validate a route plugin. (v2)
     *
     * @param object $plugin
     * @throws Exception\RuntimeException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Pre-process configuration. (v3)
     *
     * Checks for invokables, and, if found, maps them to the
     * component-specific RouteInvokableFactory; removes the invokables entry
     * before passing to the parent.
     *
     * @param array $config
     * @return void
     */
    public function configure(array $config)
    {
        if (isset($config['invokables']) && ! empty($config['invokables'])) {
            $aliases   = $this->createAliasesForInvokables($config['invokables']);
            $factories = $this->createFactoriesForInvokables($config['invokables']);

            if (! empty($aliases)) {
                $config['aliases'] = isset($config['aliases'])
                    ? array_merge($config['aliases'], $aliases)
                    : $aliases;
            }

            $config['factories'] = isset($config['factories'])
                ? array_merge($config['factories'], $factories)
                : $factories;

            unset($config['invokables']);
        }

        parent::configure($config);
    }

     /**
     * Create aliases for invokable classes.
     *
     * If an invokable service name does not match the class it maps to, this
     * creates an alias to the class (which will later be mapped as an
     * invokable factory).
     *
     * @param array $invokables
     * @return array
     */
    protected function createAliasesForInvokables(array $invokables)
    {
        $aliases = [];
        foreach ($invokables as $name => $class) {
            if ($name === $class) {
                continue;
            }
            $aliases[$name] = $class;
        }
        return $aliases;
    }

    /**
     * Create invokable factories for invokable classes.
     *
     * If an invokable service name does not match the class it maps to, this
     * creates an invokable factory entry for the class name; otherwise, it
     * creates an invokable factory for the entry name.
     *
     * @param array $invokables
     * @return array
     */
    protected function createFactoriesForInvokables(array $invokables)
    {
        $factories = [];
        foreach ($invokables as $name => $class) {
            if ($name === $class) {
                $factories[$name] = RouteInvokableFactory::class;
                continue;
            }

            $factories[$class] = RouteInvokableFactory::class;
        }
        return $factories;
    }
}
