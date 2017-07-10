<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 2017-07-09
 * Time: 21:37
 */

namespace OpenEMR\Core;

//require_once dirname(__FILE__) . '/../../../interface/globals.php';

use OpenEMR\Admin\Event\AdminSubscriber;
use OpenEMR\Core\Event\HeaderLoadedEvent;
use OpenEMR\Sample\Event\SampleSubscriber;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Config\FileLocator;

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
            $this->container = new ContainerBuilder(new ParameterBag());
            $this->getContainer()->addCompilerPass(new RegisterListenersPass());
            $this->getContainer()->register('event_dispatcher', EventDispatcher::class);
            $this->loadServiceConfig();

            /** @var EventDispatcher $ed */
            $ed = $this->getContainer()->get('event_dispatcher');
            $ed->addSubscriber(new SampleSubscriber());
        }
    }

    /**
     * Handle loading the services config file
     *
     * Low level stuff, needs more abstraction - RD 2017-07-09
     */
    private function loadServiceConfig()
    {
        $loader = new YamlFileLoader($this->container, new FileLocator($GLOBALS['webserver_root']));
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
    public function getDispatcher()
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->container->get('event_dispatcher');
        return $dispatcher;
    }
}