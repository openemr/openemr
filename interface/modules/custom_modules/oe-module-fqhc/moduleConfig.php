<?php

/**
 * FQHC module information.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

return [
    'name' => 'FQHC',
    'description' => 'UDS-oriented data capture and a modern, responsive, role-aware UI '
        . 'layered additively on the ONC-certified core.',
    'version' => '0.1.0',
    'author' => 'OpenEMR FQHC project',
    'license' => 'GPL-3.0',
    'acl_category' => 'patients',
    'acl_section' => 'demo',

    // Module dependencies
    'require' => [
        'openemr' => '>=7.0.0',
    ],

    // Database tables created by this module (none yet — added in later steps)
    'tables' => [],

    // Menu items added by this module (also injected at runtime via MenuEvent)
    'menu' => [
        [
            'label' => 'FQHC',
            'menu_id' => 'fqhc0',
            'acl' => ['patients', 'demo'],
            'url' => '/interface/modules/custom_modules/oe-module-fqhc/public/index.php',
        ],
    ],

    // Installation hooks
    'install' => [
        'sql' => 'sql/install.sql',
    ],
];
