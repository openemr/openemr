<?php

/**
 * Isolated tests for the ERA claim payment value objects: parsing ParseERA's
 * claim array, the report styles and the ERA date format.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EraPosting;

use OpenEMR\Billing\EraPosting\ClaimIssue;
use OpenEMR\Billing\EraPosting\ClaimPayment;
use OpenEMR\Billing\EraPosting\ClaimPosting;
use OpenEMR\Billing\EraPosting\ClaimPostingResult;
use OpenEMR\Billing\EraPosting\ReportStyle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClaimPaymentTest extends TestCase
{
    public function testParsesTheClaimArrayFromParseEra(): void
    {
        $claim = ClaimPayment::fromParsedEra([
            'check_number' => 'CHK 100',
            'check_date' => '20261020',
            'production_date' => '20261019',
            'our_claim_id' => '42-7',
            'claim_status_code' => '1',
            'payer_claim_id' => 'ICN123',
            'dos' => '20261001',
            'claim_date' => '20260930',
            'patient_fname' => 'ANNE',
            'patient_lname' => 'SAMPLE',
            'warnings' => "MOA segment at claim level ignored.\n",
            'crossover' => 1,
            'svc' => [
                [
                    'code' => '99213',
                    'mod' => '25',
                    'chg' => 100.0,
                    'paid' => 70.0,
                    'allowed' => 90.0,
                    'remark' => 'N130',
                    'adj' => [
                        ['group_code' => 'CO', 'reason_code' => '45', 'amount' => 10.0],
                        ['group_code' => 'PR', 'reason_code' => '2', 'amount' => 20.0],
                    ],
                ],
                ['code' => 'Claim', 'mod' => '', 'chg' => 0.0, 'paid' => -5.0, 'adj' => []],
            ],
        ]);

        $this->assertSame('CHK 100', $claim->checkNumber);
        $this->assertSame('20261020', $claim->checkDate);
        $this->assertSame('20261019', $claim->productionDate);
        $this->assertSame('42-7', $claim->ourClaimId);
        $this->assertSame('1', $claim->claimStatusCode);
        $this->assertSame('ICN123', $claim->payerClaimId);
        $this->assertSame('20261001', $claim->serviceDate, 'The service line date wins over the claim date');
        $this->assertSame('ANNE', $claim->patientFirstName);
        $this->assertSame('SAMPLE', $claim->patientLastName);
        $this->assertSame("MOA segment at claim level ignored.\n", $claim->warnings);
        $this->assertSame(1, $claim->crossover);
        $this->assertCount(2, $claim->serviceLines);

        [$line, $claimLine] = $claim->serviceLines;
        $this->assertSame('99213', $line->code);
        $this->assertSame('25', $line->modifier);
        $this->assertSame(100.0, $line->charge);
        $this->assertSame(70.0, $line->paid);
        $this->assertSame(90.0, $line->allowed);
        $this->assertSame('N130', $line->remark);
        $this->assertCount(2, $line->adjustments);
        $this->assertSame('PR', $line->adjustments[1]->groupCode);
        $this->assertSame('2', $line->adjustments[1]->reasonCode);
        $this->assertSame(20.0, $line->adjustments[1]->amount);

        $this->assertSame('Claim', $claimLine->code);
        $this->assertSame(-5.0, $claimLine->paid);
        $this->assertNull($claimLine->allowed);
        $this->assertNull($claimLine->remark);
        $this->assertSame([], $claimLine->adjustments);
    }

    public function testFallsBackToTheClaimDateAndDefaults(): void
    {
        $claim = ClaimPayment::fromParsedEra([
            'claim_date' => '20260930',
            'svc' => ['not a line', ['code' => '99213', 'adj' => ['not an adjustment']]],
        ]);

        $this->assertSame('20260930', $claim->serviceDate);
        $this->assertSame('', $claim->checkNumber);
        $this->assertSame(0, $claim->crossover);
        $this->assertCount(1, $claim->serviceLines);
        $this->assertSame(0.0, $claim->serviceLines[0]->charge);
        $this->assertSame([], $claim->serviceLines[0]->adjustments);
    }

    public function testNoServiceLinesOrDates(): void
    {
        $claim = ClaimPayment::fromParsedEra(['svc' => 'none']);

        $this->assertSame('', $claim->serviceDate);
        $this->assertSame([], $claim->serviceLines);
    }

    #[DataProvider('dateProvider')]
    public function testParseDate(string $input, string $expected): void
    {
        $this->assertSame($expected, ClaimPosting::parseDate($input));
    }

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dateProvider(): array
    {
        return [
            'ERA yyyymmdd' => ['20261020', '2026-10-20'],
            'already yyyy-mm-dd' => ['2026-10-20', '2026-10-20'],
            'with a time after it' => ['2026-10-20 13:45:00', '2026-10-20'],
            'padded' => ['  20261020  ', '2026-10-20'],
            'empty' => ['', ''],
            'not a date' => ['Oct 20 2026', ''],
        ];
    }

    public function testReportStylesKeepThePageClasses(): void
    {
        $this->assertSame('infdetail', ReportStyle::Info->cssClass());
        $this->assertSame('errdetail', ReportStyle::Error->cssClass());
        $this->assertSame('olddetail', ReportStyle::Existing->cssClass());
        $this->assertSame('newdetail', ReportStyle::Posted->cssClass());
    }

    public function testResultReportsItsIssues(): void
    {
        $result = new ClaimPostingResult(
            pid: 0,
            encounter: 0,
            invoiceNumber: '42-7',
            level: 1,
            claimStatusCode: '1',
            sessionId: 0,
            issues: [ClaimIssue::ClaimNotFound],
            paymentsPosted: 0.0,
            adjustmentsPosted: 0.0,
            chargesAdded: 0.0,
            levelClosed: false,
            secondaryQueued: false,
            lines: [],
        );

        $this->assertTrue($result->hasIssue(ClaimIssue::ClaimNotFound));
        $this->assertFalse($result->hasIssue(ClaimIssue::AlreadyPosted));
    }
}
