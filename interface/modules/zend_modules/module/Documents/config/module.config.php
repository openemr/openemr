<?php

/**
 * interface/modules/zend_modules/module/Documents/config/module.config.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Basil PT <basil@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Documents;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
use Documents\Controller\DocumentsController;
use Documents\Model\DocumentsTable;
use Interop\Container\ContainerInterface;

return [
    'controllers' => [
        'factories' => [
            DocumentsController::class => fn(ContainerInterface $container, $requestedName): \Documents\Controller\DocumentsController => new DocumentsController($container->get(DocumentsTable::class))
        ],

    ],

    'router' => [
        'routes' => [
            'documents' => [
                'type'    => Segment::class,
                'options' => [
                        // zend framework 3 get's rid of the old /:controller terminology however to be backwards compatible
                        // with the links here... we are going to reference the documents controller.
                        'route'    => '/documents/documents[/:action][/:id][/:download][/:doencryption][/:key]',
                        'constraints' => [
                            'action'        => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'id'            => '[0-9]+',
                            'download'      => '[0-1]+',
                            'doencryption'  => '[0-1]+',
                            'key'           => '[a-zA-Z][a-zA-Z0-9_-]*',
                        ],
                        'defaults' => [
                            'controller' => DocumentsController::class,
                            'action'     => 'list',
                        ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'documents' => __DIR__ . '/../view/',
        ],
        'template_map' => [
            'documents/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ],
        'strategies' => [
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ],
    ],
    // These plugins classes get added as methods onto the module controllers.  So you can reference inside a controller
    // that extends AbstractActionController.  An example below:
    // $this->Documents() as it uses (in ZF3) AbstractActionController->AbstractController->__call to call the plugin's code.  Similar to duck-typing or mixins
    // from other frameworks/languages.
    // @see https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Model_View_Controller/Controller_Plugins.html for more details.
    'controller_plugins' => [
        'factories' => [
            'Documents' => fn(ContainerInterface $container, $requestedName): \Documents\Plugin\Documents => new Plugin\Documents($container)
        ]
    ],
    'service_manager' => [
        'factories' => [
            DocumentsTable::class =>  function (ContainerInterface $container, $requestedName) {
                $table = new DocumentsTable();
                return $table;
            },
            // this class is used in other places such as the CCR module, etc so we have to expose it again.
            // TODO: we can turn this into a factory so we don't have the dup code here...
            DocumentsController::class => fn(ContainerInterface $container, $requestedName): \Documents\Controller\DocumentsController => new DocumentsController($container->get(DocumentsTable::class))
        ]


    ]
];
