<?php

/**
 * The encounter file lock names its database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\BillingUtilities;
use PHPUnit\Framework\TestCase;

class ClaimFileLockNameTest extends TestCase
{
    public function testShortDatabaseStaysInTheName(): void
    {
        $name = BillingUtilities::claimFileLockName('openemr', '10', '20');

        self::assertSame('claim-file-openemr-10-20', $name);
        self::assertLessThanOrEqual(64, strlen($name));
    }

    public function testLongDatabaseUsesADigest(): void
    {
        $database = str_repeat('clinic', 12);
        $name = BillingUtilities::claimFileLockName($database, '10', '20');

        self::assertStringStartsWith('cf-', $name);
        self::assertStringEndsWith('-10-20', $name);
        self::assertLessThanOrEqual(64, strlen($name));
        self::assertNotSame(
            BillingUtilities::claimFileLockName('other', '10', '20'),
            $name
        );
    }

    public function testSameEncounterInOneDatabaseSharesTheLock(): void
    {
        self::assertSame(
            BillingUtilities::claimFileLockName('openemr', '10', '20'),
            BillingUtilities::claimFileLockName('openemr', '10', '20')
        );
    }
}
