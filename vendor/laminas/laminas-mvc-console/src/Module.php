<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console;

class Module
{
    /**
     * Provide default configuration.
     *
     * @param return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'controller_plugins' => $provider->getPluginConfig(),
            'service_manager' => $provider->getDependencyConfig(),
            'console' => ['router' => ['routes' => []]],
        ];
    }
}
