<?php
/**
 * @link      http://github.com/zendframework/zend-navigation for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Navigation;

class ConfigProvider
{
    /**
     * Return general-purpose zend-navigation configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'abstract_factories' => [
                Service\NavigationAbstractServiceFactory::class,
            ],
            'aliases' => [
                'navigation' => Navigation::class,
            ],
            'delegators' => [
                'ViewHelperManager' => [
                    View\ViewHelperManagerDelegatorFactory::class,
                ],
            ],
            'factories' => [
                Navigation::class => Service\DefaultNavigationFactory::class,
            ],
        ];
    }
}
