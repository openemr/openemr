<?php

/**
 * interface/modules/zend_modules/module/Installer/config/module.config.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Shalini Balakrishnan  <shalini@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use Interop\Container\ContainerInterface;
use Laminas\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            Installer\Controller\InstallerController::class => function (ContainerInterface $container, $requestedName) {
                $instModuleTable = new Installer\Model\InstModuleTable($container);
                return new Installer\Controller\InstallerController($instModuleTable);
            },
        ]
    ],
    'service_manager' => [
        'factories' => [
            Installer\Model\InstModuleTable::class => fn(ContainerInterface $container, $requestedName) => new Installer\Model\InstModuleTable($container),
        ]
    ],
    'router' => [
        'routes' => [
            'Installer' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/Installer[/:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Installer\Controller\InstallerController::class,
                        'action'     => 'index',
                    ],
                ],
            ],

        ],
    ],
    'view_manager' => [
        'template_map' => [
             'site/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ],
        'template_path_stack' => [
            'installer' => __DIR__ . '/../view',
        ],
        'layout' => 'site/layout',
    ],
    'moduleconfig' => [

    ],

];
