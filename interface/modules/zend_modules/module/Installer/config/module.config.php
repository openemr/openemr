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
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\Db\ResultSet\ResultSet;
use Installer\Model\InstModule;
use Laminas\Db\Adapter\Adapter;

return array(
    'controllers' => array(
        'factories' => [
            Installer\Controller\InstallerController::class => function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(Adapter::class);
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new InstModule());
                $tableGateway = new Installer\Model\InstModuleTableGateway('InstModule', $dbAdapter, null, $resultSetPrototype);
                $InstModuleTable = new Installer\Model\InstModuleTable($tableGateway, $container);
                return new Installer\Controller\InstallerController($InstModuleTable);
            },
        ]
    ),
    'service_manager' => [
        'factories' => [
            Installer\Model\InstModuleTable::class => function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(Adapter::class);
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new InstModule());
                $tableGateway = new Installer\Model\InstModuleTableGateway('InstModule', $dbAdapter, null, $resultSetPrototype);
                return new Installer\Model\InstModuleTable($tableGateway, $container);
            },
        ]
    ],
    'router' => array(
        'routes' => array(
            'Installer' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/Installer[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => Installer\Controller\InstallerController::class,
                        'action'     => 'index',
                    ),
                ),
            ),

        ),
    ),
    'view_manager' => array(
        'template_map' => array(
             'site/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            'installer' => __DIR__ . '/../view',
        ),
        'layout' => 'site/layout',
    ),
    'moduleconfig' => array(

    ),

);
