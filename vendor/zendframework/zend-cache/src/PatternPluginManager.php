<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\Exception\InvalidServiceException;

/**
 * Plugin manager implementation for cache pattern adapters
 *
 * Enforces that retrieved adapters are instances of
 * Pattern\PatternInterface. Additionally, it registers a number of default
 * patterns available.
 */
class PatternPluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'callback' => Pattern\CallbackCache::class,
        'Callback' => Pattern\CallbackCache::class,
        'capture'  => Pattern\CaptureCache::class,
        'Capture'  => Pattern\CaptureCache::class,
        'class'    => Pattern\ClassCache::class,
        'Class'    => Pattern\ClassCache::class,
        'object'   => Pattern\ObjectCache::class,
        'Object'   => Pattern\ObjectCache::class,
        'output'   => Pattern\OutputCache::class,
        'Output'   => Pattern\OutputCache::class,
    ];

    protected $factories = [
        Pattern\CallbackCache::class    => InvokableFactory::class,
        Pattern\CaptureCache::class     => InvokableFactory::class,
        Pattern\ClassCache::class       => InvokableFactory::class,
        Pattern\ObjectCache::class      => InvokableFactory::class,
        Pattern\OutputCache::class      => InvokableFactory::class,

        // v2 normalized FQCNs
        'zendcachepatterncallbackcache' => InvokableFactory::class,
        'zendcachepatterncapturecache'  => InvokableFactory::class,
        'zendcachepatternclasscache'    => InvokableFactory::class,
        'zendcachepatternobjectcache'   => InvokableFactory::class,
        'zendcachepatternoutputcache'   => InvokableFactory::class,
    ];

    /**
     * Don't share by default
     *
     * @var boolean
     */
    protected $shareByDefault = false;

    /**
     * Don't share by default
     *
     * @var boolean
     */
    protected $sharedByDefault = false;

    /**
     * @var string
     */
    protected $instanceOf = Pattern\PatternInterface::class;

    /**
     * Override get to inject options as PatternOptions instance.
     *
     * {@inheritDoc}
     */
    public function get($plugin, array $options = [], $usePeeringServiceManagers = true)
    {
        if (empty($options)) {
            return parent::get($plugin, [], $usePeeringServiceManagers);
        }

        $plugin = parent::get($plugin, [], $usePeeringServiceManagers);
        $plugin->setOptions(new Pattern\PatternOptions($options));
        return $plugin;
    }

    /**
     * Override build to inject options as PatternOptions instance.
     *
     * {@inheritDoc}
     */
    public function build($plugin, array $options = null)
    {
        if (empty($options)) {
            return parent::build($plugin);
        }

        $plugin = parent::build($plugin);
        $plugin->setOptions(new Pattern\PatternOptions($options));
        return $plugin;
    }

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
     * @param mixed $plugin
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
