<?php

/**
 * A function-style background service handler for BackgroundServiceRunnerHandlerTest,
 * loaded only through the service row's require_once.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Background\Fixtures;

function recordBackgroundHandlerFunction(): void
{
    RecordingBackgroundHandler::$calls[] = 'function';
}
