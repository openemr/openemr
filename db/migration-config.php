<?php

declare(strict_types=1);

/**
 * Doctrine Migrations config file
 *
 * @link https://www.doctrine-project.org/projects/doctrine-migrations/en/3.9/reference/configuration.html#configuration
 *
 */

return [
    'custom_template' => 'db/migration-template.php.tpl',
    'migrations_paths' => [
        'Db\\Migrations' => 'db/Migrations',
    ],
    'table_storage' => [
        'table_name' => 'migrations',
        'execution_time_column_name' => 'execution_duration_ms',
    ],
];
