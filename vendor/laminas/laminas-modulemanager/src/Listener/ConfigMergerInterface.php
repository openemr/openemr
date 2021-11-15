<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

/**
 * Config merger interface
 */
interface ConfigMergerInterface
{
    /**
     * getMergedConfig
     *
     * @param  bool $returnConfigAsObject
     * @return mixed
     */
    public function getMergedConfig($returnConfigAsObject = true);

    /**
     * setMergedConfig
     *
     * @param  array $config
     * @return ConfigMergerInterface
     */
    public function setMergedConfig(array $config);
}
