<?php
/**
 * @link      http://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail;

class ConfigProvider
{
    /**
     * Retrieve configuration for zend-mail package.
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
     * Retrieve dependency settings for zend-mail package.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'factories' => [
                Protocol\SmtpPluginManager::class => Protocol\SmtpPluginManagerFactory::class,
            ],
        ];
    }
}
