<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Storage;

use Zend\Cache\Exception;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for cache plugins
 *
 * Enforces that plugins retrieved are instances of
 * Plugin\PluginInterface. Additionally, it registers a number of default
 * plugins available.
 */
class PluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'clear_expired_by_factor' => Plugin\ClearExpiredByFactor::class,
        'clearexpiredbyfactor'    => Plugin\ClearExpiredByFactor::class,
        'clearExpiredByFactor'    => Plugin\ClearExpiredByFactor::class,
        'ClearExpiredByFactor'    => Plugin\ClearExpiredByFactor::class,
        'exception_handler'       => Plugin\ExceptionHandler::class,
        'exceptionhandler'        => Plugin\ExceptionHandler::class,
        'exceptionHandler'        => Plugin\ExceptionHandler::class,
        'ExceptionHandler'        => Plugin\ExceptionHandler::class,
        'ignore_user_abort'       => Plugin\IgnoreUserAbort::class,
        'ignoreuserabort'         => Plugin\IgnoreUserAbort::class,
        'ignoreUserAbort'         => Plugin\IgnoreUserAbort::class,
        'IgnoreUserAbort'         => Plugin\IgnoreUserAbort::class,
        'optimize_by_factor'      => Plugin\OptimizeByFactor::class,
        'optimizebyfactor'        => Plugin\OptimizeByFactor::class,
        'optimizeByFactor'        => Plugin\OptimizeByFactor::class,
        'OptimizeByFactor'        => Plugin\OptimizeByFactor::class,
        'serializer'              => Plugin\Serializer::class,
        'Serializer'              => Plugin\Serializer::class
    ];

    protected $factories = [
        Plugin\ClearExpiredByFactor::class           => InvokableFactory::class,
        Plugin\ExceptionHandler::class               => InvokableFactory::class,
        Plugin\IgnoreUserAbort::class                => InvokableFactory::class,
        Plugin\OptimizeByFactor::class               => InvokableFactory::class,
        Plugin\Serializer::class                     => InvokableFactory::class,

        // v2 normalized FQCNs
        'zendcachestoragepluginclearexpiredbyfactor' => InvokableFactory::class,
        'zendcachestoragepluginexceptionhandler'     => InvokableFactory::class,
        'zendcachestoragepluginignoreuserabort'      => InvokableFactory::class,
        'zendcachestoragepluginoptimizebyfactor'     => InvokableFactory::class,
        'zendcachestoragepluginserializer'           => InvokableFactory::class,
    ];

    /**
     * Do not share by default (v3)
     *
     * @var array
     */
    protected $sharedByDefault = false;

    /**
     * Do not share by default (v2)
     *
     * @var array
     */
    protected $shareByDefault = false;

    /**
     * @var string
     */
    protected $instanceOf = Plugin\PluginInterface::class;

    /**
     * {@inheritdoc}
     */
    public function validate($instance)
    {
        if ($instance instanceof $this->instanceOf) {
            // we're okay
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Plugin\PluginInterface',
            (is_object($instance) ? get_class($instance) : gettype($instance)),
            __NAMESPACE__
        ));
    }

    /**
     * Validate the plugin
     *
     * Checks that the plugin loaded is an instance of Plugin\PluginInterface.
     *
     * @param  mixed $plugin
     * @return void
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
