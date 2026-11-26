<?php

/**
 * Tests for the claim filing facts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\ClaimFilingFacts;
use PHPUnit\Framework\TestCase;

class ClaimFilingFactsTest extends TestCase
{
    public function testNumbersFromEverySourceOnceInOrder(): void
    {
        $out = ClaimFilingFacts::payerClaimNumbers(
            [['ref_value' => 'PAYER99'], ['ref_value' => 'PAYER100']],
            [['ClaimID' => 'PAYER99'], ['ClaimID' => 'PAYER200']],
            [['payer_claim_number' => 'PAYER200'], ['payer_claim_number' => 'PAYER300']]
        );
        $this->assertSame(['PAYER99', 'PAYER100', 'PAYER200', 'PAYER300'], $out);
    }

    public function testEmptyAndMissingNumbersAreSkipped(): void
    {
        $out = ClaimFilingFacts::payerClaimNumbers(
            [['ref_value' => ''], ['ref_value' => '  '], []],
            [['ClaimID' => null]],
            [['payer_claim_number' => ' PAYER99 ']]
        );
        $this->assertSame(['PAYER99'], $out);
    }

    public function testNoSourcesGiveNoNumbers(): void
    {
        $this->assertSame([], ClaimFilingFacts::payerClaimNumbers([], [], []));
    }

    public function testTheEarliestPayerReceiptIsTaken(): void
    {
        $out = ClaimFilingFacts::firstPayerReceipt([
            ['source_kind' => 'Payer', 'source_received_date' => '2026-02-02'],
            ['source_kind' => 'Payer', 'source_received_date' => '2026-01-28'],
        ]);
        $this->assertSame('2026-01-28', $out);
    }

    public function testAClearinghouseIsNotAPayerReceipt(): void
    {
        $out = ClaimFilingFacts::firstPayerReceipt([
            ['source_kind' => 'Clearinghouse', 'source_received_date' => '2026-01-27'],
            ['source_kind' => 'Payer', 'source_received_date' => '2026-01-28'],
        ]);
        $this->assertSame('2026-01-28', $out);
    }

    public function testNoPayerOrNoRealDateGivesNothing(): void
    {
        $this->assertSame('', ClaimFilingFacts::firstPayerReceipt([]));
        $this->assertSame('', ClaimFilingFacts::firstPayerReceipt([
            ['source_kind' => 'Clearinghouse', 'source_received_date' => '2026-01-27'],
        ]));
        $this->assertSame('', ClaimFilingFacts::firstPayerReceipt([
            ['source_kind' => 'Payer', 'source_received_date' => '2026-13-40'],
            ['source_kind' => 'Payer', 'source_received_date' => null],
        ]));
    }
}