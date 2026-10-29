<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$platform of method Doctrine\\\\DBAL\\\\Schema\\\\MySQLSchemaManager\\:\\:getMariaDb1027ColumnDefault\\(\\) has typehint with deprecated class Doctrine\\\\DBAL\\\\Platforms\\\\MariaDb1027Platform\\:
This class will be merged with \\{@see MariaDBPlatform\\} in 4\\.0 because support for MariaDB
            releases prior to 10\\.4\\.3 will be dropped\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Schema/MySQLSchemaManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Parameter \\$event of method Symfony\\\\Component\\\\EventDispatcher\\\\Debug\\\\WrappedListener\\:\\:__invoke\\(\\) has typehint with deprecated class Symfony\\\\Component\\\\EventDispatcher\\\\Event\\:
since Symfony 4\\.3, use "Symfony\\\\Contracts\\\\EventDispatcher\\\\Event" instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/symfony/event-dispatcher/Debug/WrappedListener.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
