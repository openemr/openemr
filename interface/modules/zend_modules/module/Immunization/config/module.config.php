<?php

namespace Immunization;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
use Immunization\Controller\ImmunizationController;
use Interop\Container\ContainerInterface;
use Immunization\Model\ImmunizationTable;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Immunization\Model\Immunization;

return array(
    'controllers' => array(
        'factories' => [
            ImmunizationController::class => function (ContainerInterface $container, $requestedName) {
                return new ImmunizationController($container->get(ImmunizationTable::class));
            }
        ],
    ),

    'router' => array(
        'routes' => array(
            'immunization' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/immunization[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => ImmunizationController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'service_manager' => [
        'factories' => array(
            \Immunization\Model\ImmunizationTable::class =>  function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Immunization());
                $tableGateway = new TableGateway('module_menu', $dbAdapter, null, $resultSetPrototype);
                $table = new ImmunizationTable($tableGateway);
                return $table;
            }
        ),
    ],

    'view_manager' => array(
        'template_path_stack' => array(
            'immunization' => __DIR__ . '/../view/',
        ),
        'template_map' => array(
            'immunization/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ),
    ),
);
