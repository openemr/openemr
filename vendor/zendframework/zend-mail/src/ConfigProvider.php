<?php
/**
 * @link      http://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
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
