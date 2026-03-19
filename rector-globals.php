<?php

declare(strict_types=1);

use OpenEMR\Rector\Rules\GlobalsToOEGlobalsBagRector;
use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withBootstrapFiles([
        __DIR__ . '/rector-bootstrap.php',
    ])
    ->withPaths([
        __DIR__ . '/ccr',
        __DIR__ . '/contrib',
        __DIR__ . '/controllers',
        __DIR__ . '/custom',
        __DIR__ . '/gacl',
        __DIR__ . '/interface',
        __DIR__ . '/library',
        __DIR__ . '/modules',
        __DIR__ . '/portal',
        __DIR__ . '/sites',
        __DIR__ . '/sphere',
        __DIR__ . '/sql_patch.php',
        __DIR__ . '/sql_upgrade.php',
        __DIR__ . '/src',
        __DIR__ . '/templates',
        __DIR__ . '/version.php',
    ])
    ->withParallel(
        timeoutSeconds: 120,
        maxNumberOfProcess: 12,
        jobSize: 12
    )
    ->withPhpVersion(PhpVersion::PHP_82)
    ->withRules([
        GlobalsToOEGlobalsBagRector::class,
    ])
    ->withSkip([
        __DIR__ . '/interface/globals.php',
        __DIR__ . '/library/sql.inc.php',
        __DIR__ . '/library/classes/Installer.class.php',
        __DIR__ . '/library/ajax/sql_server_status.php',
        __DIR__ . '/library/smarty_legacy/smarty/internals/core.assign_smarty_interface.php',
        __DIR__ . '/sites/default/config.php',
        __DIR__ . '/src/Core/OEGlobalsBag.php',
    ]);
