#!/usr/bin/env php
<?php

/**
 * CLI entry point for generating PHPStan type aliases
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use OpenEMR\Common\Command\GeneratePhpstanTypesCommand;
use OpenEMR\Common\Command\RootCliGuard;
use Symfony\Component\Console\Application;

// Refuse to run as root — see RootCliGuard.
RootCliGuard::assertNotRoot();

$application = new Application('openemr-phpstan-types', '1.0.0');
$application->add(new GeneratePhpstanTypesCommand());
$application->setDefaultCommand('openemr:generate-phpstan-types', true);
$application->run();
