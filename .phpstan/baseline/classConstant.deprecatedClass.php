<?php declare(strict_types = 1);

$ignoreErrors = [];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\Cache\\\\DoctrineProvider\\:
Use Doctrine\\\\Common\\\\Cache\\\\Psr6\\\\DoctrineProvider instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/cache/lib/Doctrine/Common/Cache/Psr6/CacheAdapter.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\Cache\\\\Adapter\\\\DoctrineAdapter\\:
Since Symfony 5\\.4, use Doctrine\\\\Common\\\\Cache\\\\Psr6\\\\CacheAdapter instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/cache/lib/Doctrine/Common/Cache/Psr6/DoctrineProvider.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Doctrine\\\\Common\\\\ClassLoader\\:
The ClassLoader is deprecated and will be removed in version 4\\.0 of doctrine/common\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/common/src/ClassLoader.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onTransactionBegin of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onTransactionCommit of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onTransactionRollBack of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant postConnect of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Connection.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant postConnect of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Connections/PrimaryReadReplicaConnection.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaAlterTable of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaAlterTableAddColumn of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaAlterTableChangeColumn of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaAlterTableRemoveColumn of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaAlterTableRenameColumn of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaCreateTable of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaCreateTableColumn of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaDropTable of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 3,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Platforms/AbstractPlatform.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant ASSOCIATIVE of deprecated class Doctrine\\\\DBAL\\\\FetchMode\\:
Use the dedicated fetch\\*\\(\\) methods for the desired fetch mode instead\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Result.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaColumnDefinition of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 4,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Schema/AbstractSchemaManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Fetching class constant onSchemaIndexDefinition of deprecated class Doctrine\\\\DBAL\\\\Events\\.$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Schema/AbstractSchemaManager.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Doctrine\\\\DBAL\\\\Types\\\\ArrayType\\:
Use \\{@link JsonType\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Types/Type.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Doctrine\\\\DBAL\\\\Types\\\\ObjectType\\:
Use \\{@link JsonType\\} instead\\.$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/doctrine/dbal/src/Types/Type.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\Session\\\\Attribute\\\\NamespacedAttributeBag\\:
since Symfony 5\\.3$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/http-foundation/Session/Attribute/NamespacedAttributeBag.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\Routing\\\\RouteCollectionBuilder\\:
since Symfony 5\\.1, use RoutingConfigurator instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/routing/RouteCollectionBuilder.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Contracts\\\\Service\\\\Test\\\\ServiceLocatorTest\\:
since PHPUnit 9\\.6$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-dc-assessments/vendor/symfony/service-contracts/Test/ServiceLocatorTest.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\ExpressionRequestMatcher\\:
since Symfony 6\\.2, use "Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\\\ExpressionRequestMatcher" instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/symfony/http-foundation/ExpressionRequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\HttpFoundation\\\\RequestMatcher\\:
since Symfony 6\\.2, use ChainRequestMatcher instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-healthlink/vendor/symfony/http-foundation/RequestMatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderGenerator\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Extension/AbstractExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderGenerator\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/dependency-injection/Loader/PhpFileLoader.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderGenerator\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-kernel/Bundle/BundleExtension.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\HttpKernel\\\\DependencyInjection\\\\AddAnnotatedClassesToCachePass\\:
since Symfony 7\\.1, to be removed in 8\\.0$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-kernel/DependencyInjection/AddAnnotatedClassesToCachePass.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\Config\\\\Builder\\\\ConfigBuilderGenerator\\:
since Symfony 7\\.4$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-kernel/Kernel.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\DependencyInjection\\\\Loader\\\\XmlFileLoader\\:
since Symfony 7\\.4, use another loader instead$#',
    'count' => 2,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-module-ihi/vendor/symfony/http-kernel/Kernel.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\EventDispatcher\\\\Event\\:
since Symfony 4\\.3, use "Symfony\\\\Contracts\\\\EventDispatcher\\\\Event" instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/symfony/event-dispatcher/Debug/TraceableEventDispatcher.php',
];
$ignoreErrors[] = [
    'message' => '#^Access to constant on deprecated class Symfony\\\\Component\\\\EventDispatcher\\\\Event\\:
since Symfony 4\\.3, use "Symfony\\\\Contracts\\\\EventDispatcher\\\\Event" instead$#',
    'count' => 1,
    'path' => __DIR__ . '/../../interface/modules/custom_modules/oe-openplanit-module/vendor/symfony/event-dispatcher/DependencyInjection/RegisterListenersPass.php',
];

return ['parameters' => ['ignoreErrors' => $ignoreErrors]];
