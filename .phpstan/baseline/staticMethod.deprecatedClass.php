<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to method createMysqli\\(\\) of deprecated class OpenEMR\\\\BC\\\\DatabaseConnectionFactory\\:
New code should use existing DB tooling and not directly create new connections\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/classes/Installer.class.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method createAdodb\\(\\) of deprecated class OpenEMR\\\\BC\\\\DatabaseConnectionFactory\\:
New code should use existing DB tooling and not directly create new connections\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method detectConnectionPersistenceFromGlobalState\\(\\) of deprecated class OpenEMR\\\\BC\\\\DatabaseConnectionFactory\\:
New code should use existing DB tooling and not directly create new connections\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../library/sql.inc.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method createMysqli\\(\\) of deprecated class OpenEMR\\\\BC\\\\DatabaseConnectionFactory\\:
New code should use existing DB tooling and not directly create new connections\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method detectConnectionPersistence\\(\\) of deprecated class OpenEMR\\\\BC\\\\DatabaseConnectionFactory\\:
New code should use existing DB tooling and not directly create new connections\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../portal/patient/fwk/libs/verysimple/DB/DataDriver/MySQLi.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method createDbal\\(\\) of deprecated class OpenEMR\\\\BC\\\\DatabaseConnectionFactory\\:
New code should use existing DB tooling and not directly create new connections\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/BC/Database.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method createDbal\\(\\) of deprecated class OpenEMR\\\\BC\\\\DatabaseConnectionFactory\\:
New code should use existing DB tooling and not directly create new connections\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Common/Logging/EventAuditLogger.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method createAdodb\\(\\) of deprecated class OpenEMR\\\\BC\\\\DatabaseConnectionFactory\\:
New code should use existing DB tooling and not directly create new connections\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method detectConnectionPersistenceFromGlobalState\\(\\) of deprecated class OpenEMR\\\\BC\\\\DatabaseConnectionFactory\\:
New code should use existing DB tooling and not directly create new connections\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../src/Gacl/Gacl.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
