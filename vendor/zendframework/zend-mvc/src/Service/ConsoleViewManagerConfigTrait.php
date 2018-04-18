<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use ArrayAccess;
use Interop\Container\ContainerInterface;

trait ConsoleViewManagerConfigTrait
{
    /**
     * Retrieve view_manager configuration, if present.
     *
     * @param ContainerInterface $container
     * @return array
     */
    private function getConfig(ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];

        if (isset($config['console']['view_manager'])) {
            $config = $config['console']['view_manager'];
        } elseif (isset($config['view_manager'])) {
            $config = $config['view_manager'];
        } else {
            $config = [];
        }

        return (is_array($config) || $config instanceof ArrayAccess)
            ? $config
            : [];
    }
}
