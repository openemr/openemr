#!/usr/bin/env php
<?php

/**
 * CLI entry point for generating PHPStan type aliases
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use OpenEMR\Common\Command\GeneratePhpstanTypesCommand;
use Symfony\Component\Console\Application;

$application = new Application('openemr-phpstan-types', '1.0.0');
$application->add(new GeneratePhpstanTypesCommand());
$application->setDefaultCommand('openemr:generate-phpstan-types', true);
$application->run();
