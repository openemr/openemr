<?php

/**
 * Isolated NotificationTaskManager Test
 *
 * Tests the pure static methods on NotificationTaskManager that do not
 * require a database connection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\Controller;

use OpenEMR\Modules\FaxSMS\Controller\NotificationTaskManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/NotificationTaskManager.php';

class NotificationTaskManagerTest extends TestCase
{
    /**
     * Upper bound: a reminder more than $cronIntervalHours hours before
     * its ideal send time is too early. The third column varies row by
     * row because that is the behavior under test.
     */
    #[DataProvider('upperBoundProvider')]
    public function testIsWithinCronWindowEnforcesUpperBound(int $remainHour, int $cronInterval, bool $expected): void
    {
        $this->assertSame($expected, NotificationTaskManager::isWithinCronWindow($remainHour, $cronInterval));
    }

    /**
     * No lower bound: a missed tick must still send on the next run.
     * The runner's per-event guard skips appointments that have already
     * started; the SQL dedup prevents re-sends for already-notified
     * events. So every row here is expected to pass — there's nothing
     * to vary in a third column.
     */
    #[DataProvider('catchUpProvider')]
    public function testIsWithinCronWindowAlwaysAcceptsCatchUpTicks(int $remainHour, int $cronInterval): void
    {
        $this->assertTrue(NotificationTaskManager::isWithinCronWindow($remainHour, $cronInterval));
    }

    /**
     * Sub-hour and non-positive intervals must clamp to 1h. The third
     * column is the expected decision for the clamped check.
     */
    #[DataProvider('intervalClampProvider')]
    public function testIsWithinCronWindowClampsSubHourIntervals(int $remainHour, int $cronInterval, bool $expected): void
    {
        $this->assertSame($expected, NotificationTaskManager::isWithinCronWindow($remainHour, $cronInterval));
    }

    /**
     * @return array<string, array{int, int, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function upperBoundProvider(): array
    {
        return [
            'exactly on time'                  => [0, 1, true],
            'one hour early with 1h interval'  => [1, 1, true],
            'two hours early with 1h interval' => [2, 1, false],
            'within 24h window (positive)'     => [12, 24, true],
            'at boundary of 24h window'        => [24, 24, true],
            'outside 24h window'               => [25, 24, false],
            'far outside window'               => [100, 24, false],
        ];
    }

    /**
     * @return array<string, array{int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function catchUpProvider(): array
    {
        return [
            'one hour late with 1h interval'  => [-1, 1],
            'two hours late with 1h interval' => [-2, 1],
            'far late with 1h interval'       => [-100, 1],
            'far late with 24h interval'      => [-1000, 24],
        ];
    }

    /**
     * @return array<string, array{int, int, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function intervalClampProvider(): array
    {
        return [
            'zero interval clamps to 1h'     => [1, 0, true],
            'zero interval rejects 2h early' => [2, 0, false],
            'negative interval clamps to 1h' => [0, -5, true],
        ];
    }
}
