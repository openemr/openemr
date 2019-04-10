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
*    @author  Basil PT <basil@zhservices.com>
* +------------------------------------------------------------------------------+
*/
namespace Documents;

use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Segment;
use Documents\Controller\DocumentsController;
use Documents\Model\DocumentsTable;
use Interop\Container\ContainerInterface;

return array(
    'controllers' => array(
        'factories' => [
            DocumentsController::class => function (ContainerInterface $container, $requestedName) {
                return new DocumentsController($container->get(DocumentsTable::class));
            }
        ],
        
    ),

    'router' => array(
        'routes' => array(
            'documents' => array(
                'type'    => Segment::class,
                'options' => array(
                        // zend framework 3 get's rid of the old /:controller terminology however to be backwards compatible
                        // with the links here... we are going to reference the documents controller.
                        'route'    => '/documents/documents[/:action][/:id][/:download][/:doencryption][/:key]',
                        'constraints' => array(
                            'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'id'            => '[0-9]+',
                            'download'      => '[0-1]+',
                            'doencryption'  => '[0-1]+',
                            'key'           => '[a-zA-Z][a-zA-Z0-9_-]*',
                        ),
                        'defaults' => array(
                            'controller' => DocumentsController::class,
                            'action'     => 'list',
                        ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'documents' => __DIR__ . '/../view/',
        ),
        'template_map' => array(
            'documents/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ),
    ),
    // These plugins classes get added as methods onto the module controllers.  So you can reference inside a controller
    // that extends AbstractActionController.  An example below:
    // $this->Documents() as it uses (in ZF3) AbstractActionController->AbstractController->__call to call the plugin's code.  Similar to duck-typing or mixins
    // from other frameworks/languages.
    // @see https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Model_View_Controller/Controller_Plugins.html for more details.
    'controller_plugins' => array(
        'factories' => array(
            'Documents' => function (ContainerInterface $container, $requestedName) {
                return new Plugin\Documents($container);
            }
        )
    ),
    'service_manager' => [
        'factories' => [
            DocumentsTable::class =>  function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(\Zend\Db\Adapter\Adapter::class);
                $table = new DocumentsTable($dbAdapter);
                return $table;
            },
            // this class is used in other places such as the CCR module, etc so we have to expose it again.
            // TODO: we can turn this into a factory so we don't have the dup code here...
            DocumentsController::class => function (ContainerInterface $container, $requestedName) {
                return new DocumentsController($container->get(DocumentsTable::class));
            }
        ]

        
    ]
);
