<?php

namespace Carecoordination\Factory;

use Carecoordination\Controller\EncounterccdadispatchController;
use Carecoordination\Model\EncountermanagerTable;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Carecoordination\Controller\SetupController;

/**
 * Creates instances of SetupController.  This is necessary because the controller is used both as a service
 * and a route 'controller' which duplicates the setup code.  Having the factory puts the setup code into one spot.
 */
class SetupControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new SetupController($container->get(\Carecoordination\Model\SetupTable::class));
    }
}
