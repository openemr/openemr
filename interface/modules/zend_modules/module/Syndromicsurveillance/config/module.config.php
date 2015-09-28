<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Syndromicsurveillance'    => 'Syndromicsurveillance\Controller\SyndromicsurveillanceController',
        ),
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
                        'controller' => 'Syndromicsurveillance',
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
);

?>
