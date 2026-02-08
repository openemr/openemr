<?php

namespace Ccr;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
use Ccr\Controller\CcrController;
use Ccr\Controller\ModuleconfigController;
use Interop\Container\ContainerInterface;
use Ccr\Model\CcrTable;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Ccr\Model\Ccr;
use Documents\Controller\DocumentsController;
use Laminas\Db\TableGateway\TableGateway;

return [
    'controllers' => [
        'factories' => [
            CcrController::class => fn(ContainerInterface $container, $requestedName): \Ccr\Controller\CcrController => new CcrController($container->get(CcrTable::class), $container->get(DocumentsController::class))
        ],
    ],

    'router' => [
        'routes' => [
            'ccr' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/ccr[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => CcrController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'ccr' => __DIR__ . '/../view/',
        ],
        'template_map' => [
            'ccr/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ],
        'strategies' => [
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ],
    ],
    'service_manager' => [
        'factories' => [
            // TODO: it is odd that this has to be available to the service manager to be dynamically instantiated... but its in the controller namespace.
            ModuleconfigController::class => fn(ContainerInterface $container, $requestedName): \Ccr\Controller\ModuleconfigController => new ModuleconfigController()
            ,CcrTable::class =>  function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(Adapter::class);
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Ccr());
                $tableGateway = new TableGateway('module_menu', $dbAdapter, null, $resultSetPrototype);
                $table = new CcrTable($tableGateway);
                return $table;
            }
        ]


    ]
    ,'module_dependencies' => [
        'Documents'       // Handles the saving and retrieving of embedded documents in this module.
    ]
];
