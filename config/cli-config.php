<?php

/**
 * Configuration for Doctrine Migrations
 */

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use OpenEMR\BC\Database;

$config = [
    'migrations_paths' => [
        'Db\\Migrations' => 'db/Migrations',
    ],
    'table_storage' => [
        'table_name' => 'migrations',
        'execution_time_column_name' => 'execution_duration_ms',
    ],
    'custom_template' => 'db/migration-template.php.tpl',

];

$loader = new ConfigurationArray($config);

$site = 'default'; // fixme: env or something


$fml = function(string $site) {
    require __DIR__ . "/../sites/$site/sqlconf.php";
    assert(isset($sqlconf) && is_array($sqlconf));
    $params = Database::translateLegacySqlconf($sqlconf, $site);
    return DriverManager::getConnection($params);
    var_dump(get_defined_vars());
    exit;
};
$conn = $fml($site);

// $conn = Database::instance()->getDbalConnection();
$connL = new ExistingConnection(
    connection: $conn,
);
$df = DependencyFactory::fromConnection(
    configurationLoader: $loader,
    connectionLoader: $connL,
);

var_dump("READ CLI CONFIG");
return $df;
