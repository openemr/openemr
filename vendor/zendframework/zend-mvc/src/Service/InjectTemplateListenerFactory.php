<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Interop\Container\ContainerInterface;
use Zend\Mvc\View\Http\InjectTemplateListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

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

    /**
     * Create and return InjectTemplateListener instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return InjectTemplateListener
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, InjectTemplateListener::class);
    }
}
