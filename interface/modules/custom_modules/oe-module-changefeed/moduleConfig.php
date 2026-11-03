<?php

/**
 * Change Feed module information.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ahmed Armaan <arman.ahmaed@carer.ai>
 * @copyright Copyright (c) 2026 Ahmed Armaan
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

return [
    'name' => 'Change Feed',
    'description' => 'Adds a resource change feed: serve the clinical-core rows (patient_data, form_encounter) that changed since a cursor - including deletions - through a REST endpoint that returns FHIR resource references, so integrators can sync incrementally instead of full-scan polling.',
    'version' => '1.0.0',
    'author' => 'Ahmed Armaan',
    'email' => 'arman.ahmaed@carer.ai',
    'license' => 'GPL-3.0',
    'acl_category' => 'admin',
    'acl_section' => 'users',

    // Module dependencies
    'require' => [
        'openemr' => '>=7.0.0',
    ],

    // Database tables created by this module
    'tables' => [
        'changefeed_log',
    ],

    // Installation hooks. The changefeed_log table is created here; the
    // change-capture triggers are installed from ModuleManagerListener on
    // enable, because CREATE TRIGGER cannot be expressed through the #If* SQL
    // install parser.
    'install' => [
        'sql' => 'sql/install.sql',
    ],

    // Uninstallation hooks
    'uninstall' => [
        'sql' => 'sql/uninstall.sql',
    ],
];
