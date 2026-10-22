<?php

/**
 * Isolated tests for the 837I CLM05 facility code built from the UB-04 type of bill.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\X125010837I;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class X125010837IClaimFacilityCodeTest extends TestCase
{
    #[DataProvider('typeOfBillProvider')]
    public function testClaimFacilityCode(string $typeOfBill, string $expected): void
    {
        $this->assertSame($expected, X125010837I::claimFacilityCode($typeOfBill));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function typeOfBillProvider(): array
    {
        return [
            'FQHC 0771' => ['0771', '77:A:1'],
            'RHC 0711' => ['0711', '71:A:1'],
            'hospital outpatient 0131' => ['0131', '13:A:1'],
            'default 831 without leading zero' => ['831', '83:A:1'],
            'leading zero 0831 matches 831' => ['0831', '83:A:1'],
            'replacement claim frequency' => ['0137', '13:A:7'],
            'letter frequency code' => ['081A', '81:A:A'],
            'letter frequency code without leading zero' => ['13Q', '13:A:Q'],
            'lower-case frequency code is upper-cased' => ['081a', '81:A:A'],
            'surrounding whitespace' => [' 0771 ', '77:A:1'],
            'empty' => ['', ''],
            'too short' => ['83', ''],
            'too long' => ['10771', ''],
            'letter in facility type' => ['0A31', ''],
            'not alphanumeric' => ['013#', ''],
        ];
    }
}
