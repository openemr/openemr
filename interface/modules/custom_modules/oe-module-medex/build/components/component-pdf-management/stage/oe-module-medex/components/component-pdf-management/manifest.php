<?php

declare(strict_types=1);

return [
    'key' => 'component-pdf-management',
    'aliases' => ['pdf_management'],
    'title' => 'PDF Management',
    'description' => 'PDF builder and FHIR-backed PDF mapping tools.',
    'menus' => [
        [
            'menu_id' => 'medex_pdf_manager',
            'label' => 'PDF Manager',
            'path' => '/interface/modules/custom_modules/oe-module-medex/admin/pdf/index.php',
            'locations' => ['top'],
            'acl' => ['patients', 'demo'],
        ],
    ],
];
