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

return [
    'controllers' => [
        'factories' => [
            ImmunizationController::class => fn(ContainerInterface $container, $requestedName): \Immunization\Controller\ImmunizationController => new ImmunizationController($container->get(ImmunizationTable::class))
        ],
    ],

    'router' => [
        'routes' => [
            'immunization' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/immunization[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => ImmunizationController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            \Immunization\Model\ImmunizationTable::class =>  function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(\Laminas\Db\Adapter\Adapter::class);
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Immunization());
                $tableGateway = new TableGateway('module_menu', $dbAdapter, null, $resultSetPrototype);
                $table = new ImmunizationTable($tableGateway);
                return $table;
            }
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'immunization' => __DIR__ . '/../view/',
        ],
        'template_map' => [
            'immunization/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ],
        'strategies' => [
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ],
    ],
];
