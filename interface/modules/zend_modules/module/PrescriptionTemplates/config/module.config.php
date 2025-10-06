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

return [

    'controllers' => [
        'factories' => [
            HtmlTemplatesController::class => fn(ContainerInterface $container, $requestedName): \PrescriptionTemplates\Controller\HtmlTemplatesController => new HtmlTemplatesController(),
            PdfTemplatesController::class => fn(ContainerInterface $container, $requestedName): \PrescriptionTemplates\Controller\PdfTemplatesController => new PdfTemplatesController($container)
        ]
    ],
    'router' => [
        'routes' => [
            'p_html_template' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/prescription-html-template[/:action][/:method]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'method'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => HtmlTemplatesController::class,
                        'action'     => 'default'
                    ],
                ],
            ],
            'p_pdf_template' => [

                'type'    => Segment::class,
                'options' => [
                    'route'    => '/prescription-pdf-template[/:action][/:method]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'method'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => PdfTemplatesController::class,
                        'action'     => 'default'
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'PrescriptionTemplate' => __DIR__ . '/../view',
        ],
        'template_map' => [
            'PrescriptionTemplate/layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ]

    ],
];
