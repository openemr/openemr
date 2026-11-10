<?php

/**
 * Isolated tests for Claim::hasLineAdjudication(), which decides whether the
 * 837P sends loop 2330B DTP*573: the segment is not used when loop 2430
 * reports the prior payer's adjudication by service line.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\Claim;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class ClaimLineAdjudicationTest extends TestCase
{
    /**
     * A secondary claim (payers[0] secondary, payers[1] primary) for one
     * 99213 line, with the real constructor (which hits the database)
     * bypassed and the invoice seeded the way InvoiceSummary builds it.
     *
     * @param array<string, array<string, mixed>> $detail the line's posted activity
     */
    private function makeSecondaryClaim(array $detail): Claim
    {
        $claim = new class extends Claim {
            public function __construct()
            {
            }
        };

        $claim->procs = [['code' => '99213', 'modifier' => '']];
        $claim->payers = [
            ['data' => ['type' => 'secondary']],
            ['data' => ['type' => 'primary']],
        ];
        $claim->using_modifiers = false;
        $claim->invoice = [
            '99213' => [
                'chg' => 100.00,
                'adj' => 0,
                'dtl' => ['          1000' => ['chg' => 100.00]] + $detail,
            ],
        ];

        return $claim;
    }

    public function testPrimaryPaymentOnALineIsLineAdjudication(): void
    {
        $claim = $this->makeSecondaryClaim([
            '2026-09-20  2000' => ['pmt' => 60.00, 'plv' => '1', 'src' => 'TESTCHK9'],
            '2026-09-20  2001' => ['chg' => -25.00, 'pmt' => 0, 'plv' => '1', 'rsn' => 'Adjust code 45'],
        ]);

        $this->assertTrue($claim->hasLineAdjudication(1));
    }

    public function testPrimaryAdjustmentAloneIsLineAdjudication(): void
    {
        // A claim the primary paid nothing on: only the contractual write-off.
        $claim = $this->makeSecondaryClaim([
            '2026-09-20  2001' => ['chg' => -100.00, 'pmt' => 0, 'plv' => '1', 'rsn' => 'Adjust code 45'],
        ]);

        $this->assertTrue($claim->hasLineAdjudication(1));
    }

    public function testNoActivityFromThePriorPayerIsNotLineAdjudication(): void
    {
        $claim = $this->makeSecondaryClaim([]);

        $this->assertFalse($claim->hasLineAdjudication(1));
    }
}
