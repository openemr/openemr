<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use DCarbone\PHPFHIR\Builder;
use DCarbone\PHPFHIR\Config;
use DCarbone\PHPFHIR\Config\VersionConfig;

$config = new Config(
    libraryPath: __DIR__ . '/tmp/output',
    libraryNamespacePrefix: 'OpenEMR\\FHIR',
    versions: [
        new VersionConfig(name: 'R4', schemaPath: __DIR__ . '/schemas/R4'),
    ],
    testsPath: __DIR__ . '/tmp/tests',
);

$builder = new Builder($config);
$builder->render();
