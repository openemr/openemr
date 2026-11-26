<?php

/**
 * Tests for the encounter history sources.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\EncounterHistory;
use OpenEMR\Billing\EdiHistory\EncounterHistorySources;
use PHPUnit\Framework\TestCase;

class EncounterHistorySourcesTest extends TestCase
{
    public function testBilledTakesItsDateFromTheBatchFile(): void
    {
        $out = EncounterHistorySources::billed(
            [['FileName' => 'batch.txt', 'CLM01' => '100-200', 'Fee' => '257.00', 'SvcDate' => '20260120']],
            [['FileName' => 'batch.txt', 'Date' => '20260127']]
        );
        $this->assertSame('2026-01-27', $out[0]->date);
        $this->assertSame(EncounterHistory::KIND_BILLED, $out[0]->kind);
        $this->assertSame('100-200', $out[0]->reference);
        $this->assertStringContainsString('257.00', $out[0]->detail);
    }

    public function testBilledWithNoBatchDateIsUndated(): void
    {
        $out = EncounterHistorySources::billed([['FileName' => 'b.txt', 'CLM01' => '1-2']], []);
        $this->assertSame('', $out[0]->date);
    }

    public function testAcknowledgmentKeepsTypeStatusAndTrace(): void
    {
        $out = EncounterHistorySources::acknowledged([
            ['FileName' => 'a.999', 'RspDate' => '20260127', 'RspType' => 'f837', 'Status' => 'A', 'Trace' => '0000001010001'],
        ]);
        $this->assertSame('2026-01-27', $out[0]->date);
        $this->assertSame('Acknowledgment f837', $out[0]->title);
        $this->assertStringContainsString('Status A', $out[0]->detail);
    }

    public function testStatusNamesItsSourceAndPayerNumber(): void
    {
        $out = EncounterHistorySources::statuses([
            [
                'file_name' => 'ack.277',
                'claim_reference' => '100-200',
                'source_name' => 'EXAMPLE HEALTH PLAN',
                'source_kind' => 'Payer',
                'source_received_date' => '2026-01-28',
                'result' => 'Accepted',
                'payer_numbers' => 'PAYER99',
            ],
        ]);
        $this->assertSame('2026-01-28', $out[0]->date);
        $this->assertSame('Claim status from EXAMPLE HEALTH PLAN (Payer)', $out[0]->title);
        $this->assertStringContainsString('Accepted', $out[0]->detail);
        $this->assertStringContainsString('PAYER99', $out[0]->detail);
    }

    public function testZeroPaymentRemittanceIsStillAFact(): void
    {
        $out = EncounterHistorySources::remittances(
            [['FileName' => 'r.835', 'CLM01' => '100-200', 'Status' => '4', 'Pmt' => '0.00', 'PtResp' => '0.00', 'ClaimID' => 'PAYER99', 'Payer' => 'PLAN']],
            [['FileName' => 'r.835', 'Date' => '20260710']]
        );
        $this->assertCount(1, $out);
        $this->assertSame('2026-07-10', $out[0]->date);
        $this->assertStringContainsString('paid 0.00', $out[0]->detail);
        $this->assertStringContainsString('PAYER99', $out[0]->detail);
    }

    public function testSameContentAndClaimGivesOneDedupeKey(): void
    {
        $claims = [['FileName' => 'a.835', 'CLM01' => '100-200'], ['FileName' => 'b.835', 'CLM01' => '100-200']];
        $files = [['FileName' => 'a.835', 'Sha256' => 'abc'], ['FileName' => 'b.835', 'Sha256' => 'abc']];
        $out = EncounterHistorySources::remittances($claims, $files);
        $this->assertSame($out[0]->dedupeKey, $out[1]->dedupeKey);
        $this->assertCount(1, EncounterHistory::assemble($out));
    }

    public function testDifferentClaimsOfOneFileAreNotCollapsed(): void
    {
        $claims = [['FileName' => 'a.835', 'CLM01' => '100-200'], ['FileName' => 'a.835', 'CLM01' => '100-201']];
        $out = EncounterHistorySources::remittances($claims, [['FileName' => 'a.835', 'Sha256' => 'abc']]);
        $this->assertCount(2, EncounterHistory::assemble($out));
    }

    public function testDatesThatAreNotDatesAreLeftEmpty(): void
    {
        $out = EncounterHistorySources::acknowledged([['RspDate' => '20261340'], ['RspDate' => 'TODAY']]);
        $this->assertSame('', $out[0]->date);
        $this->assertSame('', $out[1]->date);
    }

    public function testPostedUsesThePostingDate(): void
    {
        $out = EncounterHistorySources::posted([
            ['post_date' => '2026-02-12', 'post_time' => '2026-02-10 09:00:00', 'payer_type' => 1, 'code' => '92014', 'pay_amount' => '120.00', 'adj_amount' => '0.00'],
        ]);
        $this->assertSame('2026-02-12', $out[0]->date);
        $this->assertSame('Posted to insurance level 1', $out[0]->title);
        $this->assertStringContainsString('Code 92014', $out[0]->detail);
    }

    public function testPostedFallsBackToThePostTimeDay(): void
    {
        $out = EncounterHistorySources::posted([
            ['post_date' => null, 'post_time' => '2026-02-10 09:00:00', 'payer_type' => 0],
        ]);
        $this->assertSame('2026-02-10', $out[0]->date);
        $this->assertSame('Posted to patient', $out[0]->title);
    }

    public function testPostedClaimLevelAndReasons(): void
    {
        $out = EncounterHistorySources::posted([
            [
                'post_date' => '2026-02-12', 'payer_type' => 1, 'code' => '',
                'pay_amount' => '0.00', 'adj_amount' => '257.00',
                'reason_code' => '29', 'memo' => 'Timely filing', 'reference' => 'CHK1',
                'payer_claim_number' => 'PAYER99',
            ],
        ]);
        $this->assertStringContainsString('Claim level', $out[0]->detail);
        $this->assertStringContainsString('reason 29', $out[0]->detail);
        $this->assertStringContainsString('memo Timely filing', $out[0]->detail);
        $this->assertStringContainsString('check CHK1', $out[0]->detail);
        $this->assertSame('PAYER99', $out[0]->reference);
    }

    public function testPostedOmitsWhatTheRowDoesNotSay(): void
    {
        $out = EncounterHistorySources::posted([
            ['post_date' => '2026-02-12', 'payer_type' => 1, 'code' => '92014', 'pay_amount' => '10.00', 'adj_amount' => '0.00'],
        ]);
        $this->assertStringNotContainsString('reason', $out[0]->detail);
        $this->assertStringNotContainsString('check', $out[0]->detail);
        $this->assertStringNotContainsString('payer claim number', $out[0]->detail);
    }

    public function testNoPostedRowsGivesNothing(): void
    {
        $this->assertSame([], EncounterHistorySources::posted([]));
    }

    public function testAdjustmentsKeepGroupReasonAndAmount(): void
    {
        $text = EncounterHistorySources::adjustmentsAndRemarks([
            'svc' => [
                ['adj' => [['group_code' => 'CO', 'reason_code' => '45', 'amount' => '120.00']]],
                ['adj' => [['group_code' => 'PR', 'reason_code' => '2', 'amount' => '37.00']]],
            ],
        ]);
        $this->assertSame('adjustments CO-45 120.00, PR-2 37.00', $text);
    }

    public function testRemarkCodesAreKeptOnce(): void
    {
        $text = EncounterHistorySources::adjustmentsAndRemarks([
            'svc' => [['remark' => 'N130'], ['remark' => 'N130'], ['remark' => 'MA01']],
        ]);
        $this->assertSame('remarks N130, MA01', $text);
    }

    public function testAdjustmentsAndRemarksTogether(): void
    {
        $text = EncounterHistorySources::adjustmentsAndRemarks([
            'svc' => [['adj' => [['group_code' => 'CO', 'reason_code' => '29', 'amount' => '257.00']], 'remark' => 'N130']],
        ]);
        $this->assertSame('adjustments CO-29 257.00; remarks N130', $text);
    }

    public function testAClaimWithNoAdjustmentsOrRemarksSaysNothing(): void
    {
        $this->assertSame('', EncounterHistorySources::adjustmentsAndRemarks([]));
        $this->assertSame('', EncounterHistorySources::adjustmentsAndRemarks(['svc' => [['adj' => []]]]));
    }

    public function testRemittanceCarriesItsAdjustments(): void
    {
        $out = EncounterHistorySources::remittances(
            [['FileName' => 'r.835', 'CLM01' => '100-200', 'Pmt' => '0.00']],
            [['FileName' => 'r.835', 'Date' => '20260710']],
            ["r.835\0" . '100-200' => 'adjustments CO-29 257.00; remarks N130']
        );
        $this->assertStringContainsString('adjustments CO-29 257.00; remarks N130', $out[0]->detail);
    }

    public function testAnotherClaimsAdjustmentsAreNotBorrowed(): void
    {
        $out = EncounterHistorySources::remittances(
            [['FileName' => 'r.835', 'CLM01' => '100-201']],
            [],
            ["r.835\0" . '100-200' => 'adjustments CO-29 257.00']
        );
        $this->assertStringNotContainsString('CO-29', $out[0]->detail);
    }
}