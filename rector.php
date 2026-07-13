<?php

# https://getrector.com/documentation

declare(strict_types=1);

use OpenEMR\Rector\Rules\CatchExceptionToThrowableRector;
use OpenEMR\Rector\Rules\OEGlobalsBagTypedGettersRector;
use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withBootstrapFiles([
        __DIR__ . '/rector-bootstrap.php',
    ])
    ->withPaths([
        __DIR__ . '/Documentation',
        __DIR__ . '/apis',
        __DIR__ . '/ccdaservice',
        __DIR__ . '/ccr',
        __DIR__ . '/contrib',
        __DIR__ . '/controllers',
        __DIR__ . '/custom',
        __DIR__ . '/gacl',
        __DIR__ . '/interface',
        __DIR__ . '/library',
        __DIR__ . '/oauth2',
        __DIR__ . '/portal',
        __DIR__ . '/sites',
        __DIR__ . '/sphere',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    // oe-module-claimrev-connect is a Composer dependency
    // (claimrevolution/oe-module-claimrev-connect), relocated into this path by
    // the oe-module-installer-plugin during `composer install`. It is
    // third-party code, not maintained in this repo, so skip it the same way
    // vendor/ is skipped.
    ->withSkip([
        __DIR__ . '/interface/modules/custom_modules/oe-module-claimrev-connect',
        // ReleasePrepCommand deliberately references OpenEMR\Release\Mutator\
        // ChangelogMutator as a runtime string literal so the class is only
        // materialized when composer's dev-only autoload map is loaded. The
        // string form keeps composer-require-checker (and IDEs) from
        // treating the reference as a compile-time symbol and therefore
        // requiring the dev-side namespace to be reachable from production.
        // Rector's StringClassNameToClassConstantRector would otherwise
        // convert this literal to `::class`, defeating that boundary.
        StringClassNameToClassConstantRector::class => [
            __DIR__ . '/src/Common/Command/ReleasePrepCommand.php',
        ],
    ])
    ->withCache(
        // ensure file system caching is used instead of in-memory
        cacheClass: FileCacheStorage::class,
        // specify a path that works locally as well as on CI job runners
        cacheDirectory: '/tmp/rector'
    )
    ->withCodeQualityLevel(5)
    ->withConfiguredRule(ClassPropertyAssignToConstructorPromotionRector::class, [
        'allow_model_based_classes' => true,
        'inline_public' => false,
        'rename_property' => true,
    ])
    ->withDeadCodeLevel(5)
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
    ])
    ->withPhpSets()
    ->withTypeCoverageLevel(5);
