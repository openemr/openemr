<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Translator;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Translator.
 */
class TranslatorServiceFactory implements FactoryInterface
{
    /**
     * Create a Translator instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return Translator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        // Configure the translator
        $config     = $container->get('config');
        $trConfig   = isset($config['translator']) ? $config['translator'] : [];
        $translator = Translator::factory($trConfig);
        if ($container->has('TranslatorPluginManager')) {
            $translator->setPluginManager($container->get('TranslatorPluginManager'));
        }
        return $translator;
    }

    /**
     * laminas-servicemanager v2 factory for creating Translator instance.
     *
     * Proxies to `__invoke()`.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Translator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, Translator::class);
    }
}
