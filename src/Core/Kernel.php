<?php

/**
 * OpenEMR <https://open-emr.org>.
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

/**
 * Class Kernel.
 *
 * This is the core of OpenEMR. It is a thin class enabling service containers,
 * event dispatching for now.
 *
 * @package OpenEMR
 * @subpackage Core
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017-2022 Robert Down
 */
class Kernel
{
    /** @var ContainerBuilder */
    private $container;

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
            $definition = new Definition(EventDispatcher::class, [new Reference('service_container')]);
            $definition->setPublic(true);
            $builder->setDefinition('event_dispatcher', $definition);
            $builder->compile();
            $this->container = $builder;
        }
    }

    /**
     * Return true if the environment variable OPENEMR__ENVIRONMENT is set to dev.
     *
     * @return bool
     */
    public function isDev()
    {
        return (($_ENV['OPENEMR__ENVIRONMENT'] ?? '') === 'dev') ? true : false;
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
     * @throws \Exception
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
