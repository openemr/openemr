<?php

/**
 * X12RemoteTracker must store created_at and updated_at on a 24-hour clock.
 *
 * update() used date('h'), which is 01-12 with no meridian, so an afternoon
 * update was stored as a morning time. Both create() and update() now format
 * through formatTimestamp(), which this test checks directly.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\BillingProcessor\X12RemoteTracker;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class X12RemoteTrackerUpdateTest extends TestCase
{
    public function testFormatTimestampUsesA24HourClock(): void
    {
        // BaseService pulls in custom/code_types.inc.php at load, which queries
        // unless this constant is set. Define it before X12RemoteTracker is loaded.
        if (!defined('OPENEMR_STATIC_ANALYSIS')) {
            define('OPENEMR_STATIC_ANALYSIS', true);
        }

        // 15:04 is 03:04 on a 12-hour clock with no AM/PM, which is the bug.
        $afternoon = mktime(15, 4, 5, 6, 15, 2026);
        $this->assertNotFalse($afternoon);

        $method = new ReflectionMethod(X12RemoteTracker::class, 'formatTimestamp');
        $this->assertSame('2026-06-15 15:04:05', $method->invoke(null, $afternoon));
    }
}
