<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper\Navigation;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\View\HelperPluginManager;

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

        // Legacy Zend Framework aliases
        \Zend\View\Helper\Navigation\Breadcrumbs::class => Breadcrumbs::class,
        \Zend\View\Helper\Navigation\Links::class => Links::class,
        \Zend\View\Helper\Navigation\Menu::class => Menu::class,
        \Zend\View\Helper\Navigation\Sitemap::class => Sitemap::class,

        // v2 normalized FQCNs
        'zendviewhelpernavigationbreadcrumbs' => Breadcrumbs::class,
        'zendviewhelpernavigationlinks' => Links::class,
        'zendviewhelpernavigationmenu' => Menu::class,
        'zendviewhelpernavigationsitemap' => Sitemap::class,
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

        'laminasviewhelpernavigationbreadcrumbs' => InvokableFactory::class,
        'laminasviewhelpernavigationlinks'       => InvokableFactory::class,
        'laminasviewhelpernavigationmenu'        => InvokableFactory::class,
        'laminasviewhelpernavigationsitemap'     => InvokableFactory::class,
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
