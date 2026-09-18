#!/usr/bin/env php
<?php

/**
 * ci/e2e-video-chapters.php
 *
 * Runs OpenEMR\PHPUnit\Timeline\VideoChaptersCommand against the timeline the
 * E2e suite recorded, writing WebVTT and chapter sidecars next to the video.
 * Invoked from ci/ciLibrary.source once the recording has been extracted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

use OpenEMR\PHPUnit\Timeline\VideoChaptersCommand;
use Symfony\Component\Console\Application;

if (php_sapi_name() !== 'cli') {
    echo 'Only php cli can execute a command';
    exit(1);
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

$command = new VideoChaptersCommand();
$application = new Application('e2e-video-chapters');
$application->addCommand($command);
$application->setDefaultCommand($command->getName() ?? '', true);
$application->run();
