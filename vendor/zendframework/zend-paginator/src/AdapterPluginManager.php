<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for paginator adapters.
 *
 * Enforces that adapters retrieved are instances of
 * Adapter\AdapterInterface. Additionally, it registers a number of default
 * adapters available.
 */
class AdapterPluginManager extends AbstractPluginManager
{
    /**
     * Default aliases
     *
     * Primarily for ensuring previously defined adapters select their
     * current counterparts.
     *
     * @var array
     */
    protected $aliases = [
        'callback'       => Adapter\Callback::class,
        'Callback'       => Adapter\Callback::class,
        'dbselect'       => Adapter\DbSelect::class,
        'dbSelect'       => Adapter\DbSelect::class,
        'DbSelect'       => Adapter\DbSelect::class,
        'dbtablegateway' => Adapter\DbTableGateway::class,
        'dbTableGateway' => Adapter\DbTableGateway::class,
        'DbTableGateway' => Adapter\DbTableGateway::class,
        'null'           => Adapter\NullFill::class,
        'Null'           => Adapter\NullFill::class,
        'nullfill'       => Adapter\NullFill::class,
        'nullFill'       => Adapter\NullFill::class,
        'NullFill'       => Adapter\NullFill::class,
        'array'          => Adapter\ArrayAdapter::class,
        'Array'          => Adapter\ArrayAdapter::class,
        'iterator'       => Adapter\Iterator::class,
        'Iterator'       => Adapter\Iterator::class,
        'zendpaginatoradapternull' => Adapter\NullFill::class,
    ];

    /**
     * Default set of adapter factories
     *
     * @var array
     */
    protected $factories = [
        Adapter\Callback::class       => Adapter\Service\CallbackFactory::class,
        Adapter\DbSelect::class       => Adapter\Service\DbSelectFactory::class,
        Adapter\DbTableGateway::class => Adapter\Service\DbTableGatewayFactory::class,
        Adapter\NullFill::class       => InvokableFactory::class,
        Adapter\Iterator::class       => Adapter\Service\IteratorFactory::class,
        Adapter\ArrayAdapter::class   => InvokableFactory::class,

        // v2 normalized names

        'zendpaginatoradaptercallback'       => Adapter\Service\CallbackFactory::class,
        'zendpaginatoradapterdbselect'       => Adapter\Service\DbSelectFactory::class,
        'zendpaginatoradapterdbtablegateway' => Adapter\Service\DbTableGatewayFactory::class,
        'zendpaginatoradapternullfill'       => InvokableFactory::class,
        'zendpaginatoradapteriterator'       => Adapter\Service\IteratorFactory::class,
        'zendpaginatoradapterarrayadapter'   => InvokableFactory::class,
    ];

    protected $instanceOf = Adapter\AdapterInterface::class;

    /**
     * Validate that a plugin is an adapter (v3)
     *
     * @param mixed $plugin
     * @throws InvalidServiceException
     */
    public function validate($plugin)
    {
        if (! $plugin instanceof $this->instanceOf) {
            throw new InvalidServiceException(sprintf(
                'Plugin of type %s is invalid; must implement %s',
                (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
                Adapter\AdapterInterface::class
            ));
        }
    }

    /**
     * Validate that a plugin is an adapter (v2)
     *
     * @param mixed $plugin
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
}
