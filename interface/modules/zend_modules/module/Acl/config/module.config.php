<?php

/**
 * interface/modules/zend_modules/module/Acl/config/module.config.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jacob T.Paul <jacob@zhservices.com>
 * @author    Basil PT <basil@zhservices.com>
 * @copyright Copyright (c) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Acl;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
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
                $dbAdapter = $container->get(\Laminas\Db\Adapter\Adapter::class);
                $table = new Model\AclTable($dbAdapter);
                return $table;
            },
        ]
    ]
);
