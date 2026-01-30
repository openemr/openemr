<?php

/**
 * Dashboard Context Manager Module Information
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

return [
    'name' => 'Dashboard Context Manager',
    'description' => 'Allows users and administrators to control patient dashboard widget visibility based on care contexts such as Primary Care, Outpatient, Emergency, Specialty, and more.',
    'version' => '1.0.0',
    'author' => 'Jerry Padgett',
    'email' => 'sjpadgett@gmail.com',
    'license' => 'GPL-3.0',
    'acl_category' => 'admin',
    'acl_section' => 'users',

    // Module dependencies
    'require' => [
        'openemr' => '>=7.0.0',
    ],

    // Database tables created by this module
    'tables' => [
        'user_dashboard_context',
        'dashboard_context_definitions',
        'dashboard_widget_order',
        'dashboard_context_assignments',
        'dashboard_context_role_defaults',
        'dashboard_context_facility_defaults',
        'dashboard_context_audit_log',
    ],

    // Menu items added by this module
    'menu' => [
        [
            'label' => 'Dashboard Contexts',
            'menu_id' => 'admin',
            'acl' => ['admin', 'users'],
            'url' => '/interface/modules/custom_modules/oe-module-dashboard-context/public/admin.php',
        ],
    ],

    // Global settings added by this module
    'globals' => [
        [
            'name' => 'dashboard_context_enabled',
            'type' => 'bool',
            'default' => '1',
            'description' => 'Enable Dashboard Context Manager',
        ],
        [
            'name' => 'dashboard_context_user_can_switch',
            'type' => 'bool',
            'default' => '1',
            'description' => 'Allow users to switch their own context',
        ],
        [
            'name' => 'dashboard_context_show_widget',
            'type' => 'bool',
            'default' => '1',
            'description' => 'Show context selector widget on dashboard',
        ],
    ],

    // Installation hooks
    'install' => [
        'sql' => 'sql/install.sql',
        'php' => 'src/Install.php',
    ],

    // Uninstallation hooks
    'uninstall' => [
        'sql' => 'sql/uninstall.sql',
    ],

    // Upgrade hooks (keyed by version)
    'upgrade' => [
        '1.0.1' => [
            'sql' => 'sql/upgrade_1.0.1.sql',
        ],
    ],
];
