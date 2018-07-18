<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Translator;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * zend-servicemanager v2 factory for creating Translator instance.
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
