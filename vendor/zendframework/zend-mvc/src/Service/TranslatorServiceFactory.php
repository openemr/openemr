<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Interop\Container\ContainerInterface;
use Traversable;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\I18n\DummyTranslator;
use Zend\Mvc\I18n\Translator as MvcTranslator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Overrides the translator factory from the i18n component in order to
 * replace it with the bridge class from this namespace.
 */
class TranslatorServiceFactory implements FactoryInterface
{
    /**
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return MvcTranslator
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        // Assume that if a user has registered a service for the
        // TranslatorInterface, it must be valid
        if ($container->has('Zend\I18n\Translator\TranslatorInterface')) {
            return new MvcTranslator($container->get('Zend\I18n\Translator\TranslatorInterface'));
        }

        // Load a translator from configuration, if possible
        if ($container->has('config')) {
            $config = $container->get('config');

            // 'translator' => false
            if (array_key_exists('translator', $config) && $config['translator'] === false) {
                return new MvcTranslator(new DummyTranslator());
            }

            // 'translator' => array( ... translator options ... )
            if (array_key_exists('translator', $config)
                && ((is_array($config['translator']) && !empty($config['translator']))
                    || $config['translator'] instanceof Traversable)
            ) {
                $i18nTranslator = Translator::factory($config['translator']);
                $i18nTranslator->setPluginManager($container->get('TranslatorPluginManager'));
                $container->setService('Zend\I18n\Translator\TranslatorInterface', $i18nTranslator);
                return new MvcTranslator($i18nTranslator);
            }
        }

        // If ext/intl is not loaded, return a dummy translator
        if (!extension_loaded('intl')) {
            return new MvcTranslator(new DummyTranslator());
        }

        // For BC purposes (pre-2.3.0), use the I18n Translator
        return new MvcTranslator(new Translator());
    }

    /**
     * Create and return MvcTranslator instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return MvcTranslator
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, MvcTranslator::class);
    }
}
