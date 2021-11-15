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

class ViewTemplatePathStackFactory implements FactoryInterface
{
    /**
     * Create the template path stack view resolver
     *
     * Creates a Laminas\View\Resolver\TemplatePathStack and populates it with the
     * ['view_manager']['template_path_stack'] and sets the default suffix with the
     * ['view_manager']['default_template_suffix']
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return ViewResolver\TemplatePathStack
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $config = $container->get('config');

        $templatePathStack = new ViewResolver\TemplatePathStack();

        if (is_array($config) && isset($config['view_manager'])) {
            $config = $config['view_manager'];
            if (is_array($config)) {
                if (isset($config['template_path_stack'])) {
                    $templatePathStack->addPaths($config['template_path_stack']);
                }
                if (isset($config['default_template_suffix'])) {
                    $templatePathStack->setDefaultSuffix($config['default_template_suffix']);
                }
            }
        }

        return $templatePathStack;
    }
}
