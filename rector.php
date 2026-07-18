<?php

# https://getrector.com/documentation

declare(strict_types=1);

use OpenEMR\Rector\Rules\CatchExceptionToThrowableRector;
use OpenEMR\Rector\Rules\OEGlobalsBagTypedGettersRector;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ArrowFunction\ArrowFunctionDelegatingCallToFirstClassCallableRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withBootstrapFiles([
        __DIR__ . '/rector-bootstrap.php',
    ])
    // Keep this list aligned with the paths in phpstan.neon.dist.
    ->withPaths([
        __DIR__ . '/Documentation',
        __DIR__ . '/_rest_routes.inc.php',
        __DIR__ . '/acl_upgrade.php',
        __DIR__ . '/admin.php',
        __DIR__ . '/apis',
        __DIR__ . '/ccdaservice',
        __DIR__ . '/ccr',
        __DIR__ . '/cli',
        __DIR__ . '/config',
        __DIR__ . '/contrib',
        __DIR__ . '/controller.php',
        __DIR__ . '/controllers',
        __DIR__ . '/custom',
        __DIR__ . '/gacl',
        __DIR__ . '/index.php',
        __DIR__ . '/interface',
        __DIR__ . '/ippf_upgrade.php',
        __DIR__ . '/library',
        __DIR__ . '/oauth2',
        __DIR__ . '/portal',
        __DIR__ . '/setup.php',
        __DIR__ . '/sites',
        __DIR__ . '/sphere',
        __DIR__ . '/sql_patch.php',
        __DIR__ . '/sql_upgrade.php',
        __DIR__ . '/src',
        __DIR__ . '/templates',
        __DIR__ . '/tests',
        __DIR__ . '/tools/release/bin',
        __DIR__ . '/tools/release/src',
        __DIR__ . '/version.php',
    ])
    // oe-module-claimrev-connect is a Composer dependency
    // (claimrevolution/oe-module-claimrev-connect), relocated into this path by
    // the oe-module-installer-plugin during `composer install`. It is
    // third-party code, not maintained in this repo, so skip it the same way
    // vendor/ is skipped.
    ->withSkip([
        __DIR__ . '/interface/modules/custom_modules/oe-module-claimrev-connect',
        // The Firehed container compiler inlines closure source code into the
        // compiled container; first-class callables have no closure body to
        // extract, so this rule breaks container compilation for definitions
        // under config/.
        ArrowFunctionDelegatingCallToFirstClassCallableRector::class => [
            __DIR__ . '/config',
        ],
    ])
    ->withCache(
        // ensure file system caching is used instead of in-memory
        cacheClass: FileCacheStorage::class,
        // specify a path that works locally as well as on CI job runners
        cacheDirectory: '/tmp/rector'
    )
    // Scan non-composer-autoloaded class directories so type inference is
    // identical whether rector analyzes the full withPaths tree or just the
    // filenames a pre-commit hook passes. See the comment in the neon file.
    ->withPHPStanConfigs([
        __DIR__ . '/.phpstan/rector-scan.neon',
    ])
    ->withCodeQualityLevel(5)
    ->withConfiguredRule(ClassPropertyAssignToConstructorPromotionRector::class, [
        'allow_model_based_classes' => true,
        'inline_public' => false,
        'rename_property' => true,
    ])
    ->withDeadCodeLevel(5)
    // Import fully-qualified names as use statements, including in docblocks,
    // and drop imports left unused by the rewrite.
    // ->withImportNames(removeUnusedImports: true)
    // https://getrector.com/documentation/troubleshooting-parallel
    ->withParallel(
        timeoutSeconds: 120,
        maxNumberOfProcess: 12,
        jobSize: 12
    )
    // FIXME rector should pick the php version from composer.json
    // but that doesn't seem to be working, so hard-coding for now.
    ->withPhpVersion(PhpVersion::PHP_82)
    ->withRules([
        CallUserFuncArrayToVariadicRector::class,
        CatchExceptionToThrowableRector::class,
        OEGlobalsBagTypedGettersRector::class,
        SimplifyIfElseToTernaryRector::class,
        UnnecessaryTernaryExpressionRector::class,
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(5);
