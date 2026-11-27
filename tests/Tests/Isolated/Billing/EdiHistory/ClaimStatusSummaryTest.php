<?php

/**
 * Tests for the claim-status table's summary of the indexed statuses.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\ClaimStatusSummary;
use PHPUnit\Framework\TestCase;

class ClaimStatusSummaryTest extends TestCase
{
    private static function statusRow(string $reference, int $accepted, int $rejected, string $file = 'ack.277'): array
    {
        return [
            'file_name' => $file,
            'claim_reference' => $reference,
            'source_name' => 'EXAMPLE HEALTH PLAN',
            'source_kind' => 'Payer',
            'source_received_date' => '2026-01-28',
            'accepted' => $accepted,
            'rejected' => $rejected,
        ];
    }

    private function summary(array $statusRows, array $refRows = []): array
    {
        return ClaimStatusSummary::byClaim($statusRows, $refRows);
    }

    public function testNamesTheSourceAndItsReceivedDate(): void
    {
        $out = $this->summary([self::statusRow('100-200', 1, 0)]);
        $claim = $out[ClaimStatusSummary::key('ack.277', '100-200')];
        $this->assertSame('EXAMPLE HEALTH PLAN (Payer)', $claim['source']);
        $this->assertSame('2026-01-28', $claim['received']);
    }

    public function testResultShowsAcceptanceOrRejection(): void
    {
        $out = $this->summary([
            self::statusRow('a', 1, 0),
            self::statusRow('b', 0, 1),
            self::statusRow('c', 0, 0),
        ]);
        $this->assertSame('Accepted', $out[ClaimStatusSummary::key('ack.277', 'a')]['result']);
        $this->assertSame('Rejected', $out[ClaimStatusSummary::key('ack.277', 'b')]['result']);
        $this->assertSame('', $out[ClaimStatusSummary::key('ack.277', 'c')]['result']);
    }

    public function testBothAnAcceptanceAndARejectionAreShown(): void
    {
        $out = $this->summary([
            self::statusRow('100-200', 1, 0),
            self::statusRow('100-200', 0, 1),
        ]);
        $this->assertSame('Accepted, Rejected', $out[ClaimStatusSummary::key('ack.277', '100-200')]['result']);
    }

    public function testPayerNumbersAreKeptOnceInFileOrder(): void
    {
        $ref = static fn(string $v): array => [
            'file_name' => 'ack.277',
            'claim_reference' => '100-200',
            'ref_value' => $v,
        ];
        $out = $this->summary(
            [self::statusRow('100-200', 1, 0)],
            [$ref('PAYER99'), $ref('PAYER100'), $ref('PAYER99'), $ref('')]
        );
        $this->assertSame('PAYER99, PAYER100', $out[ClaimStatusSummary::key('ack.277', '100-200')]['payer_numbers']);
    }

    public function testClaimsAreKeptApartByFileAndReference(): void
    {
        $out = $this->summary([
            self::statusRow('100-200', 1, 0),
            self::statusRow('100-201', 0, 1),
            self::statusRow('100-200', 0, 1, 'other.277'),
        ]);
        $this->assertCount(3, $out);
        $this->assertSame('Accepted', $out[ClaimStatusSummary::key('ack.277', '100-200')]['result']);
        $this->assertSame('Rejected', $out[ClaimStatusSummary::key('other.277', '100-200')]['result']);
    }

    public function testASourceWithNoNameShowsNothing(): void
    {
        $row = self::statusRow('100-200', 0, 0);
        $row['source_name'] = '';
        $row['source_kind'] = 'Other';
        $row['source_received_date'] = null;
        $out = $this->summary([$row]);
        $claim = $out[ClaimStatusSummary::key('ack.277', '100-200')];
        $this->assertSame('', $claim['source']);
        $this->assertSame('', $claim['received']);
    }

    public function testNoRowsGivesNothingAndAnUnknownReferenceIsIgnored(): void
    {
        $this->assertSame([], $this->summary([], []));
        $out = $this->summary(
            [self::statusRow('100-200', 1, 0)],
            [['file_name' => 'ack.277', 'claim_reference' => '999-999', 'ref_value' => 'X']]
        );
        $this->assertSame('', $out[ClaimStatusSummary::key('ack.277', '100-200')]['payer_numbers']);
    }
}