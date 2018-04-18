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
 * Plugin manager implementation for scrolling style adapters
 *
 * Enforces that adapters retrieved are instances of
 * ScrollingStyle\ScrollingStyleInterface. Additionally, it registers a number
 * of default adapters available.
 */
class ScrollingStylePluginManager extends AbstractPluginManager
{
    /**
     * Default set of adapters
     *
     * @var array
     */
    protected $aliases = [
        'all'     => ScrollingStyle\All::class,
        'All'     => ScrollingStyle\All::class,
        'elastic' => ScrollingStyle\Elastic::class,
        'Elastic' => ScrollingStyle\Elastic::class,
        'jumping' => ScrollingStyle\Jumping::class,
        'Jumping' => ScrollingStyle\Jumping::class,
        'sliding' => ScrollingStyle\Sliding::class,
        'Sliding' => ScrollingStyle\Sliding::class,
    ];

    /**
     * Default set of adapter factories
     *
     * @var array
     */
    protected $factories = [
        ScrollingStyle\All::class     => InvokableFactory::class,
        ScrollingStyle\Elastic::class => InvokableFactory::class,
        ScrollingStyle\Jumping::class => InvokableFactory::class,
        ScrollingStyle\Sliding::class => InvokableFactory::class,

        // v2 normalized names
        'zendpaginatorscrollingstyleall'     => InvokableFactory::class,
        'zendpaginatorscrollingstyleelastic' => InvokableFactory::class,
        'zendpaginatorscrollingstylejumping' => InvokableFactory::class,
        'zendpaginatorscrollingstylesliding' => InvokableFactory::class,
    ];

    protected $instanceOf = ScrollingStyle\ScrollingStyleInterface::class;

    /**
     * Validate a plugin (v3)
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
     * Validate a plugin (v2)
     *
     * @param mixed $plugin
     * @throws Exception\InvalidArgumentException
     */
    public function validatePlugin($plugin)
    {
        try {
            $this->validate($plugin);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidArgumentException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
