<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Immunization'    => 'Immunization\Controller\ImmunizationController',
        ),
    ),

	'router' => array(
        'routes' => array(
            'immunization' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/immunization[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Immunization',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'immunization' => __DIR__ . '/../view/',
        ),
        'template_map' => array(
            'immunization/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
            'ViewFeedStrategy',
        ),
    ),
);

?>
