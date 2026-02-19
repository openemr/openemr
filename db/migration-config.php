<?php

declare(strict_types=1);

/**
 * Doctrine Migrations config file
 *
 * This file is read by config/cli-config.php.
 *
 * @link https://www.doctrine-project.org/projects/doctrine-migrations/en/3.9/reference/configuration.html#configuration
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

return [
    'custom_template' => 'db/migration-template.php.tpl',
    'migrations_paths' => [
        // A future version of this will integrate w/ the modules system and
        // pull in any vended migrations from installed/active modules.
        'OpenEMR\\Core\\Migrations' => 'db/Migrations',
    ],
    'table_storage' => [
        'table_name' => 'migrations',
        'execution_time_column_name' => 'execution_duration_ms',
    ],
];
