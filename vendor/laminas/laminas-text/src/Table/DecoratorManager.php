<?php

/**
 * @see       https://github.com/laminas/laminas-text for the canonical source repository
 * @copyright https://github.com/laminas/laminas-text/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-text/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Text\Table;

use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for text table decorators
 *
 * Enforces that decorators retrieved are instances of
 * Decorator\DecoratorInterface. Additionally, it registers a number of default
 * decorators.
 */
class DecoratorManager extends AbstractPluginManager
{
    /**
     * Default set of decorators
     *
     * @var array
     */
    protected $aliases = [
        'ascii'   => Decorator\Ascii::class,
        'Ascii'   => Decorator\Ascii::class,
        'blank'   => Decorator\Blank::class,
        'Blank'   => Decorator\Blank::class,
        'unicode' => Decorator\Unicode::class,
        'Unicode' => Decorator\Unicode::class,

        // Legacy Zend Framework aliases
        \Zend\Text\Table\Decorator\Ascii::class => Decorator\Ascii::class,
        \Zend\Text\Table\Decorator\Unicode::class => Decorator\Unicode::class,
        \Zend\Text\Table\Decorator\Blank::class => Decorator\Blank::class,

        // v2 normalized FQCNs
        'zendtexttabledecoratorascii' => Decorator\Ascii::class,
        'zendtexttabledecoratorblank' => Decorator\Blank::class,
        'zendtexttabledecoratorunicode' => Decorator\Unicode::class,
    ];


    protected $factories = [
        Decorator\Ascii::class          => InvokableFactory::class,
        Decorator\Unicode::class        => InvokableFactory::class,
        Decorator\Blank::class          => InvokableFactory::class,
        'laminastexttabledecoratorascii'   => InvokableFactory::class,
        'laminastexttabledecoratorblank'   => InvokableFactory::class,
        'laminastexttabledecoratorunicode' => InvokableFactory::class,
    ];

    protected $instanceOf = Decorator\DecoratorInterface::class;

    /**
     * {@inheritdoc} (v3)
     */
    public function validate($instance)
    {
        if ($instance instanceof $this->instanceOf) {
            // we're okay
            return;
        }

        throw new InvalidServiceException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Decorator\DecoratorInterface',
            (is_object($instance) ? get_class($instance) : gettype($instance)),
            __NAMESPACE__
        ));
    }

    /**
     * Validate the plugin (v2)
     *
     * Checks that the decorator loaded is an instance of Decorator\DecoratorInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidDecoratorException if invalid
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidDecoratorException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
