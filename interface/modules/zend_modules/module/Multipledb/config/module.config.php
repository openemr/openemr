<?php

/* +-----------------------------------------------------------------------------+
 * Copyright 2016 matrix israel
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see
 * http://www.gnu.org/licenses/licenses.html#GPL
 *    @author  Oshri Rozmarin <oshri.rozmarin@gmail.com>
 * +------------------------------------------------------------------------------+
 *
 */
namespace Multipledb;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
use Multipledb\Controller\MultipledbController;
use Multipledb\Controller\ModuleconfigController;
use Multipledb\Model\Multipledb;
use Multipledb\Model\MultipledbTable;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Interop\Container\ContainerInterface;

return array(

    /* declare all controllers */
    'controllers' => array(
        'factories' => [
            MultipledbController::class => function (ContainerInterface $container, $requestedName) {
                return new MultipledbController($container->get(MultipledbTable::class));
            },
            ModuleconfigController::class => InvokableFactory::class
        ],
    ),

    /**
     * routing configuration.
     * for more option and details - http://zf2.readthedocs.io/en/latest/in-depth-guide/understanding-routing.html?highlight=routing
     */
    'router' => array(
        'routes' => array(
            'multipledb' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/multipledb[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => MultipledbController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'multipledb' => __DIR__ . '/../view',
        ),'template_map' => array(
            'multipledb/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        )
    ),
    'service_manager' => [
        'factories' => array(
            MultipledbTable::class =>  function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(\Laminas\Db\Adapter\Adapter::class);
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Multipledb());
                $tableGateway = new TableGateway('multiple_db', $dbAdapter, null, $resultSetPrototype);
                $table = new MultipledbTable($tableGateway);
                return $table;
            }
            ,ModuleconfigController::class => InvokableFactory::class
        ),
    ]
);
