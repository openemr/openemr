<?php

/**
 * interface/modules/zend_modules/module/Application/config/module.config.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Remesh Babu S <remesh@zhservices.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application;

use Application\Controller\IndexController;
use Application\Controller\SendtoController;
use Application\Controller\SoapController;
use Application\Listener\Listener;
use Application\Listener\ModuleMenuSubscriber;
use Application\Model\ApplicationTable;
use Application\Model\SendtoTable;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Mvc\I18n\TranslatorFactory;
use Interop\Container\ContainerInterface;
use OpenEMR\Core\Kernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

//
return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            // The literal match does a simple string comparison and serves up the controller
            // when the expression matches exactly
            'application' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/application',
                    'defaults' => [
                        'controller'    => IndexController::class,
                        'action'        => 'index',
                    ],
                ],
                // child routes will load up as /application/child_route_key/ using the segment matcher which uses regex for the routers
                'may_terminate' => true,
                'child_routes' => [
                    'index' => [
                        'type'    => Segment::class,
                        'options' => [
                            'route'    => '/index[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'sendto' => [
                        'type'    => Segment::class,
                        'options' => [
                            'route'    => '/sendto[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                'controller' => SendtoController::class,
                                'action'     => 'index',
                            ],
                        ],
                    ],
                    'soap' => [
                        'type'    => Segment::class,
                        'options' => [
                            'route'    => '/soap[/:action]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[a-zA-Z_]*',
                                'val'    => '[0-9]*',
                            ],
                            'defaults' => [
                                'controller' => SoapController::class,
                                'action'     => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]
    // These plugins classes get added as methods onto the module controllers.  So you can reference inside a controller
    // that extends AbstractActionController.  An example below:
    // $this->CommonPlugin() as it uses (in ZF3) AbstractActionController->AbstractController->__call to call the plugin's code.  Similar to duck-typing or mixins
    // from other frameworks/languages.
    // in Several of the views the CommonPlugin is injected as 'commonplugin'
    // @see https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Model_View_Controller/Controller_Plugins.html for more details.
    ,'controller_plugins' => [
        'factories' => [
            'CommonPlugin' => fn(ContainerInterface $container, $requestedName): \Application\Plugin\CommonPlugin => new Plugin\CommonPlugin($container)
            ,'Phimail' => fn(ContainerInterface $container): \Application\Plugin\Phimail => new Plugin\Phimail($container)
        ]
    ]
    ,'controllers' => [
        'factories' => [
            IndexController::class => fn(ContainerInterface $container, $requestedName): \Application\Controller\IndexController => new IndexController($container->get(ApplicationTable::class)),
            SoapController::class => fn(ContainerInterface $container, $requestedName): \Application\Controller\SoapController => new SoapController($container->get(\Carecoordination\Controller\EncounterccdadispatchController::class)),
            SendtoController::class => fn(ContainerInterface $container, $requestedName): \Application\Controller\SendtoController => new SendtoController($container->get(ApplicationTable::class), $container->get(SendtoTable::class))
        ]
    ],
    'service_manager' => [
        'factories' => [
            Listener::class => InvokableFactory::class,
            ApplicationTable::class => function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(\Laminas\Db\Adapter\Adapter::class);
                $table = new ApplicationTable();
                return $table;
            },
            SendtoTable::class => fn(ContainerInterface $container, $requestedName): \Application\Model\SendtoTable => new SendtoTable(),
            SendtoController::class => fn(ContainerInterface $container, $requestedName): \Application\Controller\SendtoController => new SendtoController($container->get(ApplicationTable::class), $container->get(SendtoTable::class)),
            ModuleMenuSubscriber::class => InvokableFactory::class
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'javascriptGlobals' => \Application\Helper\Javascript::class,
        ],
        'factories' => [
            'translate' => fn(\Interop\Container\ContainerInterface $container, $requestedName): \Application\Helper\TranslatorViewHelper =>
                // TODO: we should look at renaming this to be TranslatorAdapter
                new \Application\Helper\TranslatorViewHelper()
            // TODO: this used to be the Getvariables functionality.. the whole thing has a leaky abstraction and should be refactored into services instead of jumping to a controller view
            , 'sendToHie'      => fn(\Interop\Container\ContainerInterface $container, $requestedName): \Application\Helper\SendToHieHelper => new \Application\Helper\SendToHieHelper($container->get(SendtoController::class))
        ]
    ],
];
