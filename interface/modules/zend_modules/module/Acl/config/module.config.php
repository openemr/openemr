<?php
/* +-----------------------------------------------------------------------------+
*    OpenEMR - Open Source Electronic Medical Record
*    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
*
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*    @author  Jacob T.Paul <jacob@zhservices.com>
*    @author  Basil PT <basil@zhservices.com>  
*
* +------------------------------------------------------------------------------+
*/
namespace Acl;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Segment;
use Interop\Container\ContainerInterface;

return array(
    'controllers' => array(
        'factories' => [
            Controller\AclController::class => function (ContainerInterface $container, $requestedName) {
                /**
                 * @see https://stackoverflow.com/a/49275531/7884612 on tips for getting the view helpers from zf2 to zf3
                 * @see https://github.com/zendframework/zend-view/blob/master/src/Helper/EscapeHtml.php
                 */
                $escapeHtml = $container->get('ViewHelperManager')->get('escapeHtml');
                $aclTable = $container->get(Model\AclTable::class);
                return new Controller\AclController($escapeHtml, $aclTable);
            },
        ],
    ),

    'router' => array(
        'routes' => array(
            'acl' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/acl[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => Controller\AclController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'acl' => __DIR__ . '/../view/',
        ),
        'template_map' => array(
            'acl/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ),
    ),
    'service_manager' => [
        'factories' => [
            Model\AclTable::class =>  function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(\Zend\Db\Adapter\Adapter::class);
                $table = new Model\AclTable($dbAdapter);
                return $table;
            },
        ]
    ]
);
