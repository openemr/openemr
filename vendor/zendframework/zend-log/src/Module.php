<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-log for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log;

class Module
{
    /**
     * Return default zend-log configuration for zend-mvc applications.
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }

    /**
     * Register specifications for all zend-log plugin managers with the ServiceListener.
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
            'LogProcessorManager',
            'log_processors',
            'Zend\ModuleManager\Feature\LogProcessorProviderInterface',
            'getLogProcessorConfig'
        );

        $serviceListener->addServiceManager(
            'LogWriterManager',
            'log_writers',
            'Zend\ModuleManager\Feature\LogWriterProviderInterface',
            'getLogWriterConfig'
        );

        $serviceListener->addServiceManager(
            'LogFilterManager',
            'log_filters',
            'Zend\Log\Filter\LogFilterProviderInterface',
            'getLogFilterConfig'
        );

        $serviceListener->addServiceManager(
            'LogFormatterManager',
            'log_formatters',
            'Zend\Log\Formatter\LogFormatterProviderInterface',
            'getLogFormatterConfig'
        );
    }
}
