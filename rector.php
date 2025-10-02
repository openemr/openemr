<?php

# https://getrector.com/documentation

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\Config\RectorConfig;
use Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Rector\Php56\Rector\FuncCall\PowToExpRector;
use Rector\Php70\Rector\FuncCall\EregToPregMatchRector;
use Rector\Php70\Rector\FuncCall\MultiDirnameRector;
use Rector\Php70\Rector\If_\IfToSpaceshipRector;
use Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;
use Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;
use Rector\Php73\Rector\FuncCall\ArrayKeyFirstLastRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
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
        __DIR__ . '/modules',
        __DIR__ . '/oauth2',
        __DIR__ . '/portal',
        __DIR__ . '/sites',
        __DIR__ . '/sphere',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withCache(
        // ensure file system caching is used instead of in-memory
        cacheClass: FileCacheStorage::class,
        // specify a path that works locally as well as on CI job runners
        cacheDirectory: '/tmp/rector'
    )
    ->withCodeQualityLevel(5)
    ->withDeadCodeLevel(5)
    // FIXME rector should pick the php version from composer.json
    // but that doesn't seem to be working, so hard-coding for now.
    ->withPhpVersion(PhpVersion::PHP_82)
    ->withRules([
        // add rules one at a time until we can replace them with a named ruleset
        ArrayKeyExistsOnPropertyRector::class, // one of the withPhpSets rules
        ArrayKeyFirstLastRector::class, // one of the withPhpSets rules
        AssignArrayToStringRector::class, // one of the withPhpSets rules
        BinaryOpBetweenNumberAndStringRector::class, // one of the withPhpSets rules
        ChangeSwitchToMatchRector::class, // one of the withPhpSets rules
        ClassConstantToSelfClassRector::class, // one of the withPhpSets rules
        ClosureToArrowFunctionRector::class, // one of the withPhpSets rules
        ConsistentImplodeRector::class, // one of the withPhpSets rules
        CreateFunctionToAnonymousFunctionRector::class, // one of the withPhpSets rules
        DirNameFileConstantToDirConstantRector::class, // one of the withPhpSets rules
        EregToPregMatchRector::class, // one of the withPhpSets rules
        IfToSpaceshipRector::class, // one of the withPhpSets rules
        MultiDirnameRector::class, // one of the withPhpSets rules
        MultiExceptionCatchRector::class, // one of the withPhpSets rules
        PowToExpRector::class, // one of the withPhpSets rules
    ])
    ->withSkip([
        __DIR__ . '/sites/default/documents/smarty'
    ])
    ->withTypeCoverageLevel(5);
