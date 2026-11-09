#!/usr/bin/env php
<?php

/**
 * Compiles the DI container from config/ for non-dev environments. Run during
 * builds, after `composer install`.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use Firehed\Container\AutoDetect;
use Firehed\Container\Compiler;

// No RootCliGuard: Docker image builds run this as root.

chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';

// The compiler reuses an existing file instead of recompiling.
$path = AutoDetect::$compiledOutputPath;
if (file_exists($path)) {
    unlink($path);
}

$compiler = new Compiler($path);
$compiler->addDirectory('config');
$compiler->build();
