<?php
/**
 * @see       https://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-cache/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Cache\PatternPluginManager;

use Zend\Cache\Pattern;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * zend-servicemanager v2-compatible plugin manager implementation for cache pattern adapters.
 *
 * Enforces that retrieved adapters are instances of
 * Pattern\PatternInterface. Additionally, it registers a number of default
 * patterns available.
 */
class PatternPluginManagerV2Polyfill extends AbstractPluginManager
{
    use PatternPluginManagerTrait;

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
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Don't share by default
     *
     * @var bool
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
    public function get($plugin, $options = [], $usePeeringServiceManagers = true)
    {
        if (empty($options)) {
            return parent::get($plugin, [], $usePeeringServiceManagers);
        }

        $plugin = parent::get($plugin, [], $usePeeringServiceManagers);
        $plugin->setOptions(new Pattern\PatternOptions($options));
        return $plugin;
    }
}
