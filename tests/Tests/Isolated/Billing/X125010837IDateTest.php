<?php

/**
 * Isolated tests for X125010837I::x12Date() date conversion
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\X125010837I;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class X125010837IDateTest extends TestCase
{
    public function testStandardDateConversion(): void
    {
        // MMDDYY -> YYYYMMDD
        $this->assertSame('20260115', X125010837I::x12Date('011526'));
    }

    public function testDateWithDifferentValues(): void
    {
        // December 31, 2025
        $this->assertSame('20251231', X125010837I::x12Date('123125'));
    }

    public function testEmptyStringReturnsPartialOutput(): void
    {
        // With empty input, substr returns '' for each part
        $result = X125010837I::x12Date('');
        $this->assertSame('20', $result);
    }
}
