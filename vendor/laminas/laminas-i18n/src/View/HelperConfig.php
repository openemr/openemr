<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\View;

use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\ServiceManager;

/**
 * Service manager configuration for i18n view helpers.
 *
 * @deprecated since 2.7.0; replaced by ConfigProvider and Module class.
 */
class HelperConfig implements ConfigInterface
{
    /**
     * Common aliases for helpers
     * @var array
     */
    protected $aliases = [
        'currencyformat' => Helper\CurrencyFormat::class,
        'currencyFormat' => Helper\CurrencyFormat::class,
        'CurrencyFormat' => Helper\CurrencyFormat::class,
        'dateformat' => Helper\DateFormat::class,
        'dateFormat' => Helper\DateFormat::class,
        'DateFormat' => Helper\DateFormat::class,
        'numberformat' => Helper\NumberFormat::class,
        'numberFormat' => Helper\NumberFormat::class,
        'NumberFormat' => Helper\NumberFormat::class,
        'plural' => Helper\Plural::class,
        'Plural' => Helper\Plural::class,
        'translate' => Helper\Translate::class,
        'Translate' => Helper\Translate::class,
        'translateplural' => Helper\TranslatePlural::class,
        'translatePlural' => Helper\TranslatePlural::class,
        'TranslatePlural' => Helper\TranslatePlural::class,

        // Legacy Zend Framework aliases
        \Zend\I18n\View\Helper\CurrencyFormat::class => Helper\CurrencyFormat::class,
        \Zend\I18n\View\Helper\DateFormat::class => Helper\DateFormat::class,
        \Zend\I18n\View\Helper\NumberFormat::class => Helper\NumberFormat::class,
        \Zend\I18n\View\Helper\Plural::class => Helper\Plural::class,
        \Zend\I18n\View\Helper\Translate::class => Helper\Translate::class,
        \Zend\I18n\View\Helper\TranslatePlural::class => Helper\TranslatePlural::class,

        // v2 normalized FQCNs
        'zendi18nviewhelpercurrencyformat' => Helper\CurrencyFormat::class,
        'zendi18nviewhelperdateformat' => Helper\DateFormat::class,
        'zendi18nviewhelpernumberformat' => Helper\NumberFormat::class,
        'zendi18nviewhelperplural' => Helper\Plural::class,
        'zendi18nviewhelpertranslate' => Helper\Translate::class,
        'zendi18nviewhelpertranslateplural' => Helper\TranslatePlural::class,
    ];

    /**
     * Factories for included helpers.
     * @var array
     */
    protected $factories = [
        Helper\CurrencyFormat::class => InvokableFactory::class,
        Helper\DateFormat::class => InvokableFactory::class,
        Helper\NumberFormat::class => InvokableFactory::class,
        Helper\Plural::class => InvokableFactory::class,
        Helper\Translate::class => InvokableFactory::class,
        Helper\TranslatePlural::class => InvokableFactory::class,
        // Legacy (v2) due to alias resolution; canonical form of resolved
        // alias is used to look up the factory, while the non-normalized
        // resolved alias is used as the requested name passed to the factory.
        'laminasi18nviewhelpercurrencyformat' => InvokableFactory::class,
        'laminasi18nviewhelperdateformat' => InvokableFactory::class,
        'laminasi18nviewhelpernumberformat' => InvokableFactory::class,
        'laminasi18nviewhelperplural' => InvokableFactory::class,
        'laminasi18nviewhelpertranslate' => InvokableFactory::class,
        'laminasi18nviewhelpertranslateplural' => InvokableFactory::class,
    ];

    /**
     * Configure the provided service manager instance with the configuration
     * in this class.
     *
     * @param  ServiceManager $serviceManager
     * @return ServiceManager
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        if (method_exists($serviceManager, 'configure')) {
            $serviceManager->configure($this->toArray());
            return $serviceManager;
        }

        foreach ($this->factories as $name => $factory) {
            $serviceManager->setFactory($name, $factory);
        }
        foreach ($this->aliases as $alias => $target) {
            $serviceManager->setAlias($alias, $target);
        }

        return $serviceManager;
    }

    /**
     * Cast configuration to an array.
     *
     * Provided for v3 compatibility
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'aliases' => $this->aliases,
            'factories' => $this->factories,
        ];
    }
}
