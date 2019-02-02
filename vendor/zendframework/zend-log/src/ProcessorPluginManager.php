<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-log for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager for log processors.
 */
class ProcessorPluginManager extends AbstractPluginManager
{
    protected $aliases = [
        'backtrace'      => Processor\Backtrace::class,
        'psrplaceholder' => Processor\PsrPlaceholder::class,
        'referenceid'    => Processor\ReferenceId::class,
        'requestid'      => Processor\RequestId::class
    ];

    protected $factories = [
        Processor\Backtrace::class      => InvokableFactory::class,
        Processor\PsrPlaceholder::class => InvokableFactory::class,
        Processor\ReferenceId::class    => InvokableFactory::class,
        Processor\RequestId::class      => InvokableFactory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'zendlogprocessorbacktrace'      => InvokableFactory::class,
        'zendlogprocessorpsrplaceholder' => InvokableFactory::class,
        'zendlogprocessorreferenceid'    => InvokableFactory::class,
        'zendlogprocessorrequestid'      => InvokableFactory::class,
    ];

    protected $instanceOf = Processor\ProcessorInterface::class;

    /**
     * Allow many processors of the same type (v2)
     * @param bool
     */
    protected $shareByDefault = false;

    /**
     * Allow many processors of the same type (v3)
     * @param bool
     */
    protected $sharedByDefault = false;

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
     * @throws InvalidServiceException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Plugin of type %s is invalid; must implement %s\Processor\ProcessorInterface',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                __NAMESPACE__
            ));
        }
    }
}
