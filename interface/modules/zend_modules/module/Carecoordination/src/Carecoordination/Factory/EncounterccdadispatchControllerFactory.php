<?php

namespace Carecoordination\Factory;

use Carecoordination\Controller\EncounterccdadispatchController;
use Carecoordination\Model\EncountermanagerTable;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Carecoordination\Model\EncounterccdadispatchTable;

/**
 * Creates instances of EncounterccdadispatchController.  This is necessary because the controller is used both as a service
 * and a route 'controller' which duplicates the setup code.  Having the factory puts the setup code into one spot.
 */
class EncounterccdadispatchControllerFactory implements FactoryInterface
{

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new EncounterccdadispatchController($container->get(EncounterccdadispatchTable::class));
    }
}
