<?php

return array(

    /* declare all controllers */
    'controllers' => array(
        'invokables' => array(
            'Patientvalidation\Controller\Patientvalidation' => 'Patientvalidation\Controller\PatientvalidationController',
        ),
    ),

    /**
     * routing configuration.
     * for more option and details - http://zf2.readthedocs.io/en/latest/in-depth-guide/understanding-routing.html?highlight=routing
     */
    'router' => array(
        'routes' => array(
            'patientvalidation' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/patientvalidation[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Patientvalidation\Controller\patientvalidation',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'patientvalidation' => __DIR__ . '/../view',
        ),
    ),
);