<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\View\Http\InjectTemplateListener;
use Laminas\ServiceManager\Factory\FactoryInterface;

class InjectTemplateListenerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * Create and return an InjectTemplateListener instance.
     *
     * @return InjectTemplateListener
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $listener = new InjectTemplateListener();
        $config   = $container->get('config');

        if (isset($config['view_manager']['controller_map'])
            && (is_array($config['view_manager']['controller_map']))
        ) {
            $listener->setControllerMap($config['view_manager']['controller_map']);
        }

        return $listener;
    }
}
