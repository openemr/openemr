<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\View\HelperPluginManager;

/**
 * Plugin manager implementation for navigation helpers
 *
 * Enforces that helpers retrieved are instances of
 * Navigation\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
class PluginManager extends HelperPluginManager
{
    /**
     * @var string Valid instance types.
     */
    protected $instanceOf = AbstractHelper::class;

    /**
     * Default aliases
     *
     * @var string[]
     */
    protected $aliases = [
        'breadcrumbs' => Breadcrumbs::class,
        'links'       => Links::class,
        'menu'        => Menu::class,
        'sitemap'     => Sitemap::class,
    ];

    /**
     * Default factories
     *
     * @var string[]
     */
    protected $factories = [
        Breadcrumbs::class => InvokableFactory::class,
        Links::class       => InvokableFactory::class,
        Menu::class        => InvokableFactory::class,
        Sitemap::class     => InvokableFactory::class,

        // v2 canonical FQCNs

        'zendviewhelpernavigationbreadcrumbs' => InvokableFactory::class,
        'zendviewhelpernavigationlinks'       => InvokableFactory::class,
        'zendviewhelpernavigationmenu'        => InvokableFactory::class,
        'zendviewhelpernavigationsitemap'     => InvokableFactory::class,
    ];

    /**
     * @param null|ConfigInterface|ContainerInterface $configOrContainerInstance
     * @param array $v3config If $configOrContainerInstance is a container, this
     *     value will be passed to the parent constructor.
     */
    public function __construct($configOrContainerInstance = null, array $v3config = [])
    {
        $this->initializers[] = function ($first, $second) {
            // v2 vs v3 argument order
            if ($first instanceof ContainerInterface) {
                // v3
                $container = $first;
                $instance = $second;
            } else {
                // v2
                $container = $second;
                $instance = $first;
            }

            if (! $instance instanceof AbstractHelper) {
                return;
            }

            // This initializer was written with v2 functionality in mind; as such,
            // we need to test and see if we're called in a v2 context, and, if so,
            // set the service locator to the parent locator.
            //
            // Under v3, the parent locator is what is passed to the method already.
            if (! method_exists($container, 'configure') && $container->getServiceLocator()) {
                $container = $container->getServiceLocator();
            }

            $instance->setServiceLocator($container);
        };

        parent::__construct($configOrContainerInstance, $v3config);
    }
}
