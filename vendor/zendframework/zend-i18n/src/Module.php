<?php
/**
 * @link      http://github.com/zendframework/zend-i18n for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n;

class Module
{
    /**
     * Return zend-i18n configuration for zend-mvc application.
     *
     * @return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'filters'         => $provider->getFilterConfig(),
            'service_manager' => $provider->getDependencyConfig(),
            'validators'      => $provider->getValidatorConfig(),
            'view_helpers'    => $provider->getViewHelperConfig(),
        ];
    }

    /**
     * Register a specification for the TranslatorPluginManager with the ServiceListener.
     *
     * @param \Zend\ModuleManager\ModuleManager $moduleManager
     * @return void
     */
    public function init($moduleManager)
    {
        $event = $moduleManager->getEvent();
        $container = $event->getParam('ServiceManager');
        $serviceListener = $container->get('ServiceListener');

        $serviceListener->addServiceManager(
            'TranslatorPluginManager',
            'translator_plugins',
            'Zend\ModuleManager\Feature\TranslatorPluginProviderInterface',
            'getTranslatorPluginConfig'
        );
    }
}
