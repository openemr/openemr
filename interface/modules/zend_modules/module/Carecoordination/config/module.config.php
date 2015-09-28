<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Carecoordination'        => 'Carecoordination\Controller\CarecoordinationController',
            'Encounterccdadispatch'   => 'Carecoordination\Controller\EncounterccdadispatchController',
            'encountermanager'        => 'Carecoordination\Controller\EncountermanagerController',
            'Carecoordination\Setup'  => 'Carecoordination\Controller\SetupController',
            'Ccd'                     => 'Carecoordination\Controller\CcdController',
        ),
    ),

	'router' => array(
        'routes' => array(
            'carecoordination' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/carecoordination[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Carecoordination',
                        'action'     => 'index',
                    ),
                ),
            ),
            
            'encounterccdadispatch' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/encounterccdadispatch[/:action][/:id][/:val][/:id][/:val]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z_]*',
                        'val'    => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Encounterccdadispatch',
                        'action'     => 'index',
                    ),
                ),
            ),
            
            'encountermanager' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/encountermanager[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'encountermanager',
                        'action'     => 'index',
                    ),
                ),
            ),
            'setup' => array(
                'type'    => 'segment',
                'options' => array(
                     'route'    => '/carecoordination/setup[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Carecoordination\Setup',
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
                        'controller' => 'Ccd',
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
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ),
    ),
);

?>
