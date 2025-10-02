<?php

/**
 * module.config.php handles the dependency injection configuration, routes, and other config settings needed by the module.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Carecoordination;

use Carecoordination\Model\CcdaGenerator;
use Documents\Plugin\Documents;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
use Carecoordination\Controller\CarecoordinationController;
use Carecoordination\Controller\EncounterccdadispatchController;
use Carecoordination\Controller\EncountermanagerController;
use Carecoordination\Controller\SetupController;
use Carecoordination\Controller\CcdController;
use Carecoordination\Model\CarecoordinationTable;
use Carecoordination\Model\EncounterccdadispatchTable;
use Carecoordination\Model\EncountermanagerTable;
use Carecoordination\Model\SetupTable;
use Carecoordination\Model\CcdTable;
use Carecoordination\Form\ModuleconfigForm;
use Carecoordination\Factory\EncounterccdadispatchControllerFactory;
use Carecoordination\Factory\SetupControllerFactory;
use Carecoordination\Controller\ModuleconfigController;
use Interop\Container\ContainerInterface;
use Application\Plugin\CommonPlugin;
use Documents\Controller\DocumentsController;
use Carecoordination\Listener\CCDAEventsSubscriber;

return array(
    'controllers' => array(
        'factories' => [
            CarecoordinationController::class => fn(ContainerInterface $container, $requestedName): \Carecoordination\Controller\CarecoordinationController => new CarecoordinationController($container->get(CarecoordinationTable::class), $container->get(DocumentsController::class)),
            EncountermanagerController::class =>  fn(ContainerInterface $container, $requestedName): \Carecoordination\Controller\EncountermanagerController => new EncountermanagerController($container->get(\Carecoordination\Model\EncountermanagerTable::class)),
            // we use factories because the controller code is used in two places.  ZF isolates the controller services into
            // their own scope and are not available from outside the module.  The factory let's us share the instantiation code.
            EncounterccdadispatchController::class =>  EncounterccdadispatchControllerFactory::class,
            SetupController::class => SetupControllerFactory::class,
            CcdController::class => fn(ContainerInterface $container, $requestedName): \Carecoordination\Controller\CcdController => new CcdController($container->get(CcdTable::class), $container->get(CarecoordinationTable::class), $container->get(\Documents\Model\DocumentsTable::class), $container->get(DocumentsController::class))
        ],
    ),

    'router' => array(
        'routes' => array(
            'carecoordination' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/carecoordination[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => CarecoordinationController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
            'setup' => array(
                'type'    => Segment::class,
                'options' => array(
                     'route'    => '/carecoordination/setup[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => SetupController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
            'encounterccdadispatch' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/encounterccdadispatch[/:action][/:id][/:val][/:id][/:val]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z_]*',
                        'val'    => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => EncounterccdadispatchController::class,
                        'action'     => 'index',
                    ),
                ),
            ),

            'encountermanager' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/encountermanager[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => EncountermanagerController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
            'ccd' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/ccd[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => CcdController::class,
                        'action'     => 'upload',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'carecoordination' => __DIR__ . '/../view/',
        ),
        'template_map' => array(
            'carecoordination/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'carecoordination/layout/mapper' => __DIR__ . '/../view/layout/mapper.phtml',
            'carecoordination/layout/encountermanager' => __DIR__ . '/../view/layout/encountermanager.phtml',
            'carecoordination/layout/setup' => __DIR__ . '/../view/layout/setup.phtml',
        ),
        // @see https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Model_View_Controller/View_Rendering_Strategies.html
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ),
    ),

    'service_manager' => [
        'factories' => array(
            CarecoordinationTable::class =>  fn(ContainerInterface $container, $requestedName): \Carecoordination\Model\CarecoordinationTable => new CarecoordinationTable(),
            EncounterccdadispatchTable::class =>  function (ContainerInterface $container, $requestedName) {
                $applicationTable = $container->get(\Application\Model\ApplicationTable::class);
                return new EncounterccdadispatchTable($applicationTable);
            },
            EncountermanagerTable::class =>  fn(ContainerInterface $container, $requestedName): \Carecoordination\Model\EncountermanagerTable => new EncountermanagerTable(),
            SetupTable::class =>  fn(ContainerInterface $container, $requestedName): \Carecoordination\Model\SetupTable => new SetupTable(),
            CcdTable::class =>  fn(ContainerInterface $container, $requestedName): \Carecoordination\Model\CcdTable => new CcdTable(),
            ModuleconfigForm::class => fn(ContainerInterface $container, $requestedName): \Carecoordination\Form\ModuleconfigForm => new ModuleconfigForm($container->get(\Laminas\Db\Adapter\Adapter::class)),
            // so this isn't really a 'controller' class used as a route 'controller' but more to reuse component code for other modules...
            ModuleconfigController::class => fn(ContainerInterface $container, $requestedName): \Carecoordination\Controller\ModuleconfigController => new ModuleconfigController($container->get(\Laminas\Db\Adapter\Adapter::class)),
            SetupController::class => SetupControllerFactory::class,
            EncounterccdadispatchController::class => EncounterccdadispatchControllerFactory::class,
            CCDAEventsSubscriber::class => fn(ContainerInterface $container, $requestedName): \Carecoordination\Listener\CCDAEventsSubscriber => new CCDAEventsSubscriber($container->get(CcdaGenerator::class)),
            CcdaGenerator::class => fn(ContainerInterface $container, $requestedName): \Carecoordination\Model\CcdaGenerator => new CcdaGenerator($container->get(EncounterccdadispatchTable::class))
        ),
    ]
    // These plugins classes get added as methods onto the module controllers.  So you can reference inside a controller
    // that extends AbstractActionController.  An example below:
    // $this->Documents() as it uses (in ZF3) AbstractActionController->AbstractController->__call to call the plugin's code.  Similar to duck-typing or mixins
    // from other frameworks/languages.
    // @see https://olegkrivtsov.github.io/using-zend-framework-3-book/html/en/Model_View_Controller/Controller_Plugins.html for more details.
    // TODO: Note this is a weird dependency used in the CarecoordinationController class that should be revisited
    ,'controller_plugins' => array(
        'factories' => array(
            'Documents' => fn(ContainerInterface $container, $requestedName): \Documents\Plugin\Documents => new \Documents\Plugin\Documents($container)
        )
    )
    ,'module_dependencies' => [
        'Ccr'
        ,'Immunization'
        ,'Syndromicsurveillance'
        , 'Documents'       // Handles the saving and retrieving of embedded documents in this module.
    ]
);
