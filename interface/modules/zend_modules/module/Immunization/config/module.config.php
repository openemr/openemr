<?php

namespace Immunization;

use Immunization\Controller\ImmunizationController;
use Immunization\Model\ImmunizationTable;
use Interop\Container\ContainerInterface;
use Laminas\Router\Http\Segment;

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
            ImmunizationTable::class => fn (ContainerInterface $container, $requestedName) => new ImmunizationTable(),
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
