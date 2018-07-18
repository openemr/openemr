<?php
return array(

    'controllers' => array(
        'invokables' => array(
            'PrescriptionTemplates\Controller\HtmlTemplates' => 'PrescriptionTemplates\Controller\HtmlTemplatesController',
            'PrescriptionTemplates\Controller\PdfTemplates' => 'PrescriptionTemplates\Controller\PdfTemplatesController'
        )
    ),
    'router' => array(
        'routes' => array(
            'p_html_template' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/prescription-html-template[/:action][/:method]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'method'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'PrescriptionTemplates\Controller\HtmlTemplates',
                        'action'     => 'default'
                    ),
                ),
            ),
            'p_pdf_template' => array(

                'type'    => 'segment',
                'options' => array(
                    'route'    => '/prescription-pdf-template[/:action][/:method]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'method'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'PrescriptionTemplates\Controller\PdfTemplates',
                        'action'     => 'default'
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'PrescriptionTemplate' => __DIR__ . '/../view',
        ),
        'template_map' => array(
            'PrescriptionTemplate/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        )

    ),
);

