<?php
/**
 * Copyright (C) 2018 Amiel Elboim <amielel@matrix.co.il>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */

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
