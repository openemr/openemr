<?php

namespace Syndromicsurveillance;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Syndromicsurveillance\Controller\SyndromicsurveillanceController;
use Syndromicsurveillance\Model\Syndromicsurveillance;
use Syndromicsurveillance\Model\SyndromicsurveillanceTable;

return [
    'controllers' => [
        'factories' => [
            SyndromicsurveillanceController::class => fn(ContainerInterface $container, $requestedName): \Syndromicsurveillance\Controller\SyndromicsurveillanceController => new SyndromicsurveillanceController($container->get(SyndromicsurveillanceTable::class))
        ]
    ],

    'router' => [
        'routes' => [
            'syndromicsurveillance' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/syndromicsurveillance[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => SyndromicsurveillanceController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'syndromicsurveillance' => __DIR__ . '/../view/',
        ],
        'template_map' => [
            'syndromicsurveillance/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ],
        'strategies' => [
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ],
    ],
    'service_manager' => [
        'factories' => [
            SyndromicsurveillanceTable::class =>  function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(\Laminas\Db\Adapter\Adapter::class);
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Syndromicsurveillance());
                $table = new SyndromicsurveillanceTable();
                return $table;
            }
        ],
    ]
];
