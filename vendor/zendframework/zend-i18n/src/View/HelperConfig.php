<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\View;

use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\ServiceManager\ServiceManager;

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
        'zendi18nviewhelpercurrencyformat' => InvokableFactory::class,
        'zendi18nviewhelperdateformat' => InvokableFactory::class,
        'zendi18nviewhelpernumberformat' => InvokableFactory::class,
        'zendi18nviewhelperplural' => InvokableFactory::class,
        'zendi18nviewhelpertranslate' => InvokableFactory::class,
        'zendi18nviewhelpertranslateplural' => InvokableFactory::class,
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
