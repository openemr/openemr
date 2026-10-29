<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Call to method deleteMultiple\\(\\) of deprecated interface Doctrine\\\\Common\\\\Cache\\\\MultiDeleteCache\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/cache/lib/Doctrine/Common/Cache/Psr6/CacheAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method fetchMultiple\\(\\) of deprecated interface Doctrine\\\\Common\\\\Cache\\\\MultiGetCache\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/cache/lib/Doctrine/Common/Cache/Psr6/CacheAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method saveMultiple\\(\\) of deprecated interface Doctrine\\\\Common\\\\Cache\\\\MultiPutCache\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/cache/lib/Doctrine/Common/Cache/Psr6/CacheAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method createDatabasePlatformForVersion\\(\\) of deprecated interface Doctrine\\\\DBAL\\\\VersionAwarePlatformDriver\\:
All drivers will have to be aware of the server version in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getServerVersion\\(\\) of deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method startQuery\\(\\) of deprecated interface Doctrine\\\\DBAL\\\\Logging\\\\SQLLogger\\:
Use \\{@see \\\\Doctrine\\\\DBAL\\\\Logging\\\\Middleware\\} or implement
           \\{@see \\\\Doctrine\\\\DBAL\\\\Driver\\\\Middleware\\} instead\\.$#',
    'count' => 8,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method stopQuery\\(\\) of deprecated interface Doctrine\\\\DBAL\\\\Logging\\\\SQLLogger\\:
Use \\{@see \\\\Doctrine\\\\DBAL\\\\Logging\\\\Middleware\\} or implement
           \\{@see \\\\Doctrine\\\\DBAL\\\\Driver\\\\Middleware\\} instead\\.$#',
    'count' => 9,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getServerVersion\\(\\) of deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/Middleware/AbstractConnectionMiddleware.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method createDatabasePlatformForVersion\\(\\) of deprecated interface Doctrine\\\\DBAL\\\\VersionAwarePlatformDriver\\:
All drivers will have to be aware of the server version in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Driver/Middleware/AbstractDriverMiddleware.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getQuotedName\\(\\) of deprecated interface Doctrine\\\\DBAL\\\\Schema\\\\Constraint\\:
Use \\{@see ForeignKeyConstraint\\}, \\{@see Index\\} or \\{@see UniqueConstraint\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method __getInitializer\\(\\) of deprecated interface Doctrine\\\\Common\\\\Proxy\\\\Proxy\\:
The Proxy interface is deprecated since doctrine/common 3\\.5\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/persistence/src/Persistence/Reflection/RuntimeReflectionProperty.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method __setInitializer\\(\\) of deprecated interface Doctrine\\\\Common\\\\Proxy\\\\Proxy\\:
The Proxy interface is deprecated since doctrine/common 3\\.5\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/persistence/src/Persistence/Reflection/RuntimeReflectionProperty.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getServerVersion\\(\\) of deprecated interface Doctrine\\\\DBAL\\\\Driver\\\\ServerInfoAwareConnection\\:
The methods defined in this interface will be made part of the \\{@see Driver\\} interface
in the next major release\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/cache/Adapter/DoctrineDbalAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method build\\(\\) of deprecated interface Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderGeneratorInterface\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Loader/PhpFileLoader.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method getExtensionAlias\\(\\) of deprecated interface Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderInterface\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Loader/PhpFileLoader.php',
];
$ignoreErrors[] = [
    'message' => '#^Call to method toArray\\(\\) of deprecated interface Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderInterface\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Loader/PhpFileLoader.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
