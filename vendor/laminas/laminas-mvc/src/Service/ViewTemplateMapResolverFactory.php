<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Resolver as ViewResolver;

class ViewTemplateMapResolverFactory implements FactoryInterface
{
    /**
     * Create the template map view resolver
     *
     * Creates a Laminas\View\Resolver\AggregateResolver and populates it with the
     * ['view_manager']['template_map']
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return ViewResolver\TemplateMapResolver
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $config = $container->get('config');
        $map = [];
        if (is_array($config) && isset($config['view_manager'])) {
            $config = $config['view_manager'];
            if (is_array($config) && isset($config['template_map'])) {
                $map = $config['template_map'];
            }
        }
        return new ViewResolver\TemplateMapResolver($map);
    }
}
