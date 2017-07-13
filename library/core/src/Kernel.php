<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 2017-07-09
 * Time: 21:37
 */

namespace OpenEMR\Core;

//require_once dirname(__FILE__) . '/../../../interface/globals.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

class Kernel
{

    /** @var ContainerBuilder */
    private $container;

    /** @var  EventDispatcher */
    private $dispatcher;

    public function __construct()
    {
        $this->prepareContainer();

    }

    /**
     * Setup the initial container
     */
    private function prepareContainer()
    {
        if (!$this->container) {
            $builder = new ContainerBuilder(new ParameterBag());
            $builder->addCompilerPass(new RegisterListenersPass());
            $definition = new Definition(ContainerAwareEventDispatcher::class, [new Reference('service_container')]);
            $builder->setDefinition('event_dispatcher', $definition);

            $this->loadServiceConfig($builder);

            $builder->compile();
            $this->container = $builder;
        }
    }

    /**
     * Handle loading the services config file
     *
     * Low level stuff, needs more abstraction - RD 2017-07-09
     */
    private function loadServiceConfig(ContainerBuilder $builder)
    {
        $loader = new YamlFileLoader($builder, new FileLocator($GLOBALS['webserver_root']));
        $loader->load('config/services.yml');
    }

    /**
     * Get the Service Container
     *
     * @return ContainerBuilder
     */
    public function getContainer()
    {
        if (!$this->container) {
            $this->prepareContainer();
        }
        return $this->container;
    }

    /**
     * Get the Event Dispatcher
     *
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        if ($this->container) {
            /** @var EventDispatcher $dispatcher */
            return $this->container->get('event_dispatcher');
        } else {
            throw new \Exception('Container does not exist');
        }
    }
}