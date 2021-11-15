<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\HttpMethodListener;
use Laminas\ServiceManager\Factory\FactoryInterface;

class HttpMethodListenerFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     * @return HttpMethodListener
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $config = $container->get('config');

        if (! isset($config['http_methods_listener'])) {
            return new HttpMethodListener();
        }

        $listenerConfig  = $config['http_methods_listener'];
        $enabled = array_key_exists('enabled', $listenerConfig)
            ? $listenerConfig['enabled']
            : true;
        $allowedMethods = (isset($listenerConfig['allowed_methods']) && is_array($listenerConfig['allowed_methods']))
            ? $listenerConfig['allowed_methods']
            : null;

        return new HttpMethodListener($enabled, $allowedMethods);
    }
}
