<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Ccr'             => 'Ccr\Controller\CcrController',
        ),
    ),

	'router' => array(
        'routes' => array(
            'ccr' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/ccr[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Ccr',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'ccr' => __DIR__ . '/../view/',
        ),
        'template_map' => array(
            'ccr/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ),
    ),
);

?>
