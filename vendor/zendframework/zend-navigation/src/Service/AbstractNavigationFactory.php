<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Navigation\Service;

use Interop\Container\ContainerInterface;
use Traversable;
use Zend\Config;
use Zend\Http\Request;
use Zend\Mvc\Router as MvcRouter;
use Zend\Navigation\Exception;
use Zend\Navigation\Navigation;
use Zend\Router\RouteMatch;
use Zend\Router\RouteStackInterface as Router;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Abstract navigation factory
 */
abstract class AbstractNavigationFactory implements FactoryInterface
{
    /**
     * @var array
     */
    protected $pages;

    /**
     * Create and return a new Navigation instance (v3).
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return Navigation
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new Navigation($this->getPages($container));
    }

    /**
     * Create and return a new Navigation instance (v2).
     *
     * @param ServiceLocatorInterface $container
     * @param null|string $name
     * @param null|string $requestedName
     * @return Navigation
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null)
    {
        $requestedName = $requestedName ?: Navigation::class;
        return $this($container, $requestedName);
    }

    /**
     * @abstract
     * @return string
     */
    abstract protected function getName();

    /**
     * @param ContainerInterface $container
     * @return array
     * @throws \Zend\Navigation\Exception\InvalidArgumentException
     */
    protected function getPages(ContainerInterface $container)
    {
        if (null === $this->pages) {
            $configuration = $container->get('config');

            if (! isset($configuration['navigation'])) {
                throw new Exception\InvalidArgumentException('Could not find navigation configuration key');
            }
            if (! isset($configuration['navigation'][$this->getName()])) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Failed to find a navigation container by the name "%s"',
                    $this->getName()
                ));
            }

            $pages       = $this->getPagesFromConfig($configuration['navigation'][$this->getName()]);
            $this->pages = $this->preparePages($container, $pages);
        }
        return $this->pages;
    }

    /**
     * @param ContainerInterface $container
     * @param array|\Zend\Config\Config $pages
     * @return null|array
     * @throws \Zend\Navigation\Exception\InvalidArgumentException
     */
    protected function preparePages(ContainerInterface $container, $pages)
    {
        $application = $container->get('Application');
        $routeMatch  = $application->getMvcEvent()->getRouteMatch();
        $router      = $application->getMvcEvent()->getRouter();
        $request     = $application->getMvcEvent()->getRequest();

        // HTTP request is the only one that may be injected
        if (! $request instanceof Request) {
            $request = null;
        }

        return $this->injectComponents($pages, $routeMatch, $router, $request);
    }

    /**
     * @param string|\Zend\Config\Config|array $config
     * @return array|null|\Zend\Config\Config
     * @throws \Zend\Navigation\Exception\InvalidArgumentException
     */
    protected function getPagesFromConfig($config = null)
    {
        if (is_string($config)) {
            if (! file_exists($config)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Config was a string but file "%s" does not exist',
                    $config
                ));
            }
            $config = Config\Factory::fromFile($config);
        } elseif ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        } elseif (! is_array($config)) {
            throw new Exception\InvalidArgumentException(
                'Invalid input, expected array, filename, or Traversable object'
            );
        }

        return $config;
    }

    /**
     * @param array $pages
     * @param RouteMatch|MvcRouter\RouteMatch $routeMatch
     * @param Router|MvcRouter\RouteStackInterface $router
     * @param null|Request $request
     * @return array
     */
    protected function injectComponents(
        array $pages,
        $routeMatch = null,
        $router = null,
        $request = null
    ) {
        $this->validateRouteMatch($routeMatch);
        $this->validateRouter($router);

        foreach ($pages as &$page) {
            $hasUri = isset($page['uri']);
            $hasMvc = isset($page['action']) || isset($page['controller']) || isset($page['route']);
            if ($hasMvc) {
                if (! isset($page['routeMatch']) && $routeMatch) {
                    $page['routeMatch'] = $routeMatch;
                }
                if (! isset($page['router'])) {
                    $page['router'] = $router;
                }
            } elseif ($hasUri) {
                if (! isset($page['request'])) {
                    $page['request'] = $request;
                }
            }

            if (isset($page['pages'])) {
                $page['pages'] = $this->injectComponents($page['pages'], $routeMatch, $router, $request);
            }
        }
        return $pages;
    }

    /**
     * Validate that a route match argument provided to injectComponents is valid.
     *
     * @param null|RouteMatch|MvcRouter\RouteMatch
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    private function validateRouteMatch($routeMatch)
    {
        if (null === $routeMatch) {
            return;
        }

        if (! $routeMatch instanceof RouteMatch
            && ! $routeMatch instanceof MvcRouter\RouteMatch
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s or %s expected by %s::injectComponents; received %s',
                RouteMatch::class,
                MvcRouter\RouteMatch::class,
                __CLASS__,
                (is_object($routeMatch) ? get_class($routeMatch) : gettype($routeMatch))
            ));
        }
    }

    /**
     * Validate that a router argument provided to injectComponents is valid.
     *
     * @param null|Router|MvcRouter\RouteStackInterface
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    private function validateRouter($router)
    {
        if (null === $router) {
            return;
        }

        if (! $router instanceof Router
            && ! $router instanceof MvcRouter\RouteStackInterface
        ) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s or %s expected by %s::injectComponents; received %s',
                RouteMatch::class,
                MvcRouter\RouteMatch::class,
                __CLASS__,
                (is_object($router) ? get_class($router) : gettype($router))
            ));
        }
    }
}
