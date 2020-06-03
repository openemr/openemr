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

namespace PrescriptionTemplates;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Segment;
use PrescriptionTemplates\Controller\HtmlTemplatesController;
use PrescriptionTemplates\Controller\PdfTemplatesController;
use Interop\Container\ContainerInterface;

return array(

    'controllers' => array(
        'factories' => [
            HtmlTemplatesController::class => function (ContainerInterface $container, $requestedName) {
                return new HtmlTemplatesController($container);
            },
            PdfTemplatesController::class => function (ContainerInterface $container, $requestedName) {
                return new PdfTemplatesController($container);
            }
        ]
    ),
    'router' => array(
        'routes' => array(
            'p_html_template' => array(
                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/prescription-html-template[/:action][/:method]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'method'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => HtmlTemplatesController::class,
                        'action'     => 'default'
                    ),
                ),
            ),
            'p_pdf_template' => array(

                'type'    => Segment::class,
                'options' => array(
                    'route'    => '/prescription-pdf-template[/:action][/:method]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'method'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => PdfTemplatesController::class,
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
