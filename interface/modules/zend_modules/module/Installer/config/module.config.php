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

use Installer\Controller\InstallerController;
use Installer\Model\InstModuleTable;
use Interop\Container\ContainerInterface;
use Laminas\Router\Http\Segment;

return [
    'controllers' => [
        'factories' => [
            InstallerController::class => function (ContainerInterface $container, $requestedName) {
                $instModuleTable = new InstModuleTable($container);
                return new InstallerController($instModuleTable);
            },
        ]
    ],
    'service_manager' => [
        'factories' => [
            InstModuleTable::class => fn(ContainerInterface $container, $requestedName) => new InstModuleTable($container),
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
                        'controller' => InstallerController::class,
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
