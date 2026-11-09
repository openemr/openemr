<?php

/**
 * Background service handlers for BackgroundServiceRunnerHandlerTest. Each
 * one records its call, so a test can tell which handler the runner reached.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Background\Fixtures;

class RecordingBackgroundHandler
{
    /** @var list<string> */
    public static array $calls = [];

    public static function run(): void
    {
        self::$calls[] = 'run';
    }

    public static function fail(): void
    {
        self::$calls[] = 'fail';
        throw new \RuntimeException('handler failed');
    }

    public function instanceRun(): void
    {
        self::$calls[] = 'instanceRun';
    }

    /**
     * @codeCoverageIgnore The runner must refuse to call this, so it never runs.
     */
    protected static function hiddenRun(): void
    {
        self::$calls[] = 'hiddenRun';
    }
}
