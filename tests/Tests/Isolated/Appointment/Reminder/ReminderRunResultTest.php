<?php

/**
 * Isolated test for the ReminderRunResult DTO.
 *
 * The DTO is read-only and its surface is small (counts, hasFailures(),
 * toArray()), so the test pins the contract — particularly the
 * snake_case keys on toArray() that PSR-3 logger context arrays rely
 * on across modules.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Appointment\Reminder;

use OpenEMR\Appointment\Reminder\ReminderRunResult;
use PHPUnit\Framework\TestCase;

class ReminderRunResultTest extends TestCase
{
    public function testStoresAllCounts(): void
    {
        $result = new ReminderRunResult(
            scanned: 10,
            inWindow: 6,
            sent: 4,
            skippedInvalid: 1,
            failed: 1,
        );

        $this->assertSame(10, $result->scanned);
        $this->assertSame(6, $result->inWindow);
        $this->assertSame(4, $result->sent);
        $this->assertSame(1, $result->skippedInvalid);
        $this->assertSame(1, $result->failed);
    }

    public function testHasFailuresIsTrueWhenFailedNonZero(): void
    {
        $result = new ReminderRunResult(scanned: 1, inWindow: 1, sent: 0, skippedInvalid: 0, failed: 1);

        $this->assertTrue($result->hasFailures());
    }

    public function testHasFailuresIsFalseWhenFailedZero(): void
    {
        $result = new ReminderRunResult(scanned: 5, inWindow: 5, sent: 4, skippedInvalid: 1, failed: 0);

        $this->assertFalse($result->hasFailures());
    }

    public function testToArrayUsesSnakeCaseKeys(): void
    {
        $result = new ReminderRunResult(scanned: 7, inWindow: 5, sent: 3, skippedInvalid: 1, failed: 1);

        $this->assertSame(
            [
                'scanned' => 7,
                'in_window' => 5,
                'sent' => 3,
                'skipped_invalid' => 1,
                'failed' => 1,
            ],
            $result->toArray(),
        );
    }
}
