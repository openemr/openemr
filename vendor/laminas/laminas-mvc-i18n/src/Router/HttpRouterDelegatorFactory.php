<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\I18n\Router;

use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class HttpRouterDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Decorate the HttpRouter factory.
     *
     * If the HttpRouter factory returns a TranslatorAwareTreeRouteStack, we
     * should inject it with a translator.
     *
     * If the MvcTranslator service is available, that translator is used.
     * If the TranslatorInterface service is available, that translator is used.
     *
     * Otherwise, we disable translation in the instance before returning it.
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param null|arry $options
     * @return \Laminas\Router\RouteStackInterface|TranslatorAwareTreeRouteStack
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $router = $callback();

        if (! $router instanceof TranslatorAwareTreeRouteStack) {
            return $router;
        }

        if ($container->has('MvcTranslator')) {
            $router->setTranslator($container->get('MvcTranslator'));
            return $router;
        }

        if ($container->has(TranslatorInterface::class)) {
            $router->setTranslator($container->get(TranslatorInterface::class));
            return $router;
        }

        $router->setTranslatorEnabled(false);

        return $router;
    }

    /**
     * laminas-servicemanager v2 compabibility
     *
     * @param ServiceLocatorInterface $container
     * @param string $name
     * @param string $requestedName
     * @param callable $callback
     * @return \Laminas\Router\RouteStackInterface|TranslatorAwareTreeRouteStack
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }
}
