#!/bin/env php
<?php declare(strict_types=1);
/**
 * Script created per php-fhir's instructions to autogenerate the FHIR types.
 *
 * See https://github.com/dcarbone/php-fhir
 */

require_once('./vendor/autoload.php');

use DCarbone\PHPFHIR\Builder;
use DCarbone\PHPFHIR\Config;
use DCarbone\PHPFHIR\Config\VersionConfig;

$config = new Config(
    libraryPath: realpath('./src/FHIR/R4'),
    versions: [
        new VersionConfig(name: 'R4', schemaPath: realpath('./fhir-schemas/R4')),
    ],
    testsPath: realpath('./tests/Tests/FHIR'),   // optional
);

$builder = new Builder($config);
$builder->render();
