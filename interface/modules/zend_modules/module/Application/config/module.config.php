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
use Application\Listener\Listener;
use Application\Listener\ModuleMenuSubscriber;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Mvc\I18n\TranslatorFactory;
use Interop\Container\ContainerInterface;
use OpenEmr\Core\Kernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

//
return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => Literal::class,
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => \Application\Controller\IndexController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
            // The literal match does a simple string comparison and serves up the controller
            // when the expression matches exactly
            'application' => array(
                'type'    => Literal::class,
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        'controller'    => \Application\Controller\IndexController::class,
                        'action'        => 'index',
                    ),
                ),
                // child routes will load up as /application/child_route_key/ using the segment matcher which uses regex for the routers
                'may_terminate' => true,
                'child_routes' => array(
                    'index' => array(
                        'type'    => Segment::class,
                        'options' => array(
                            'route'    => '/index[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => IndexController::class,
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'sendto' => array(
                        'type'    => Segment::class,
                        'options' => array(
                            'route'    => '/sendto[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => \Application\Controller\SendtoController::class,
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    'soap' => array(
                        'type'    => Segment::class,
                        'options' => array(
                            'route'    => '/soap[/:action]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[a-zA-Z_]*',
                                'val'    => '[0-9]*',
                            ),
                            'defaults' => array(
                                'controller' => \Application\Controller\SoapController::class,
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    )
    // These plugins classes get added as methods onto the module controllers.  So you can reference inside a controller
    // that extends AbstractActionController.  An example below:
    // $this->CommonPlugin() as it uses (in ZF3) AbstractActionController->AbstractController->__call to call the plugin's code.  Similar to duck-typing or mixins
    // from other frameworks/languages.
    // in Several of the views the CommonPlugin is injected as 'commonplugin'
    // @see https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Model_View_Controller/Controller_Plugins.html for more details.
    ,'controller_plugins' => array(
        'factories' => array(
            'CommonPlugin' => function (ContainerInterface $container, $requestedName) {
                return new Plugin\CommonPlugin($container);
            }
            ,'Phimail' => function (ContainerInterface $container) {
                return new Plugin\Phimail($container);
            }
        )
    )
    ,'controllers' => array(
        'factories' => [
            \Application\Controller\IndexController::class => function (ContainerInterface $container, $requestedName) {
                return new \Application\Controller\IndexController($container->get(\Application\Model\ApplicationTable::class));
            },
            \Application\Controller\SoapController::class => function (ContainerInterface $container, $requestedName) {
                return new \Application\Controller\SoapController($container->get(\Carecoordination\Controller\EncounterccdadispatchController::class));
            },
            \Application\Controller\SendtoController::class => function (ContainerInterface $container, $requestedName) {
                return new \Application\Controller\SendtoController($container->get(\Application\Model\ApplicationTable::class), $container->get(\Application\Model\SendtoTable::class));
            }
        ]
    ),
    'service_manager' => array(
        'factories' => array(
            Listener::class => InvokableFactory::class,
            \Application\Model\ApplicationTable::class => function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
                $table = new \Application\Model\ApplicationTable($dbAdapter);
                return $table;
            },
            \Application\Model\SendtoTable::class => function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
                $table = new \Application\Model\SendtoTable($dbAdapter);
                return $table;
            },
            \Application\Controller\SendtoController::class => function (ContainerInterface $container, $requestedName) {
                return new \Application\Controller\SendtoController($container->get(\Application\Model\ApplicationTable::class), $container->get(\Application\Model\SendtoTable::class));
            },
            ModuleMenuSubscriber::class => InvokableFactory::class
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'javascriptGlobals' => \Application\Helper\Javascript::class,
        ),
        'factories' => [
            'translate' => function (\Interop\Container\ContainerInterface $container, $requestedName) {
                // TODO: we should look at renaming this to be TranslatorAdapter
                return new \Application\Helper\TranslatorViewHelper();
            }
            // TODO: this used to be the Getvariables functionality.. the whole thing has a leaky abstraction and should be refactored into services instead of jumping to a controller view
            , 'sendToHie'      => function (\Interop\Container\ContainerInterface $container, $requestedName) {
                return new \Application\Helper\SendToHieHelper($container->get(\Application\Controller\SendtoController::class));
            }
        ]
    ),
);
