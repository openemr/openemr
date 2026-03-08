<?php

namespace Syndromicsurveillance;

use Interop\Container\ContainerInterface;
use Syndromicsurveillance\Controller\SyndromicsurveillanceController;
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
            SyndromicsurveillanceTable::class => fn(ContainerInterface $container, $requestedName): SyndromicsurveillanceTable => new SyndromicsurveillanceTable()
        ],
    ]
];
