<?php

/**
 * Isolated test for GetServiceOtherCounts() when the Fax/SMS module classes
 * cannot be loaded (module disabled or removed while its flags are still on).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class ServiceOtherCountsTest extends TestCase
{
    /**
     * Runs in its own process so no other test has loaded the module's
     * classes; the module namespace is not otherwise autoloadable here,
     * which is the state of a disabled module.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testReturnsZeroCountsWhenModuleClassesAreNotLoadable(): void
    {
        require_once __DIR__ . '/../../../../../library/dated_reminder_functions.php';

        $globals = OEGlobalsBag::getInstance();
        $globals->set('oefax_enable_fax', 1);
        $globals->set('oefax_enable_sms', 1);

        self::assertFalse(class_exists(AppDispatch::class));

        // Compare by key: the order the function builds the keys in is not
        // part of its contract.
        $counts = GetServiceOtherCounts();
        self::assertCount(3, $counts);
        self::assertSame(0, $counts['faxCnt'] ?? null);
        self::assertSame(0, $counts['smsCnt'] ?? null);
        self::assertSame(0, $counts['serviceTotal'] ?? null);
    }
}
