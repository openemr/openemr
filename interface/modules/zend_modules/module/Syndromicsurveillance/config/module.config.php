<?php
namespace Syndromicsurveillance;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Router\Http\Segment;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Syndromicsurveillance\Controller\SyndromicsurveillanceController;
use Syndromicsurveillance\Model\Syndromicsurveillance;
use Syndromicsurveillance\Model\SyndromicsurveillanceTable;

return array(
    'controllers' => array(
        'factories' => [
            SyndromicsurveillanceController::class => function (ContainerInterface $container, $requestedName) {
                return new SyndromicsurveillanceController($container->get(SyndromicsurveillanceTable::class));
            },
            PdfTemplatesController::class => InvokableFactory::class,
        ]
    ),

    'router' => array(
        'routes' => array(
            'syndromicsurveillance' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/syndromicsurveillance[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => SyndromicsurveillanceController::class,
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'syndromicsurveillance' => __DIR__ . '/../view/',
        ),
        'template_map' => array(
            'syndromicsurveillance/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ),
    ),
    'service_manager' => [
        'factories' => array(
            SyndromicsurveillanceTable::class =>  function (ContainerInterface $container, $requestedName) {
                $dbAdapter = $container->get(\Zend\Db\Adapter\Adapter::class);
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Syndromicsurveillance());
                $tableGateway = new TableGateway('module_menu', $dbAdapter, null, $resultSetPrototype);
                $table = new SyndromicsurveillanceTable($tableGateway);
                return $table;
            }
        ),
    ]
);
