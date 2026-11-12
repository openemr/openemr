<?php

/**
 * Database tests for ClaimPosting: posting one ERA claim payment to its
 * invoice, as sl_eob_process.php does for each claim it lists.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing\EraPosting;

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\EraCheckPosting;
use OpenEMR\Billing\EraPosting\Adjustment;
use OpenEMR\Billing\EraPosting\ClaimIssue;
use OpenEMR\Billing\EraPosting\ClaimPayment;
use OpenEMR\Billing\EraPosting\ClaimPosting;
use OpenEMR\Billing\EraPosting\ClaimPostingResult;
use OpenEMR\Billing\EraPosting\ReportDetail;
use OpenEMR\Billing\EraPosting\ReportMessage;
use OpenEMR\Billing\EraPosting\ServiceLine;
use OpenEMR\Billing\EraReversal;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\PaymentProcessing\Recorder;
use PHPUnit\Framework\TestCase;

class ClaimPostingTest extends TestCase
{
    /** Synthetic ids, clear of the demo data. */
    private const PID = 99990041;
    private const ENC = 99990141;
    private const PRIMARY = '99990341';
    private const SECONDARY = '99990342';
    private const DOS = '2026-09-15';
    private const CHECK = 'POSTCHK1';
    private const CHECK_DATE = '20261020';

    private int $sessionId = 0;

    protected function setUp(): void
    {
        SessionWrapperFactory::getInstance()->getActiveSession()->set(
            'authUserID',
            QueryUtils::fetchSingleValue('SELECT id FROM users ORDER BY id LIMIT 1', 'id')
        );
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'Anne', 'Posting', NOW())",
            [self::PID, random_bytes(16)]
        );
        QueryUtils::sqlInsert(
            'INSERT INTO form_encounter (pid, encounter, date, last_level_billed, last_level_closed) VALUES (?, ?, ?, 1, 0)',
            [self::PID, self::ENC, self::DOS . ' 00:00:00']
        );
        foreach ([['primary', self::PRIMARY], ['secondary', self::SECONDARY]] as [$type, $provider]) {
            QueryUtils::sqlInsert(
                'INSERT INTO insurance_data (pid, type, provider, date) VALUES (?, ?, ?, ?)',
                [self::PID, $type, $provider, '2026-01-01']
            );
        }
        $this->addCharge('99213', '', 100.00);
        $this->sessionId = QueryUtils::sqlInsert(
            'INSERT INTO ar_session (payer_id, user_id, reference, check_date, deposit_date, pay_total, modified_time,'
            . " global_amount, payment_type, adjustment_code, post_to_date, patient_id, payment_method)"
            . " VALUES (?, 1, ?, ?, ?, 70, NOW(), 0, 'insurance', 'insurance_payment', ?, 0, 'electronic')",
            [self::PRIMARY, 'ePay - ' . self::CHECK, self::CHECK_DATE, self::CHECK_DATE, self::CHECK_DATE]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testAPaidClaimPostsClosesTheLevelAndQueuesTheSecondary(): void
    {
        $result = $this->post($this->claim([$this->paidLine()]));

        $this->assertSame([], $result->issues);
        $this->assertSame(self::PID, $result->pid);
        $this->assertSame(self::ENC, $result->encounter);
        $this->assertSame(self::PID . '.' . self::ENC, $result->invoiceNumber);
        $this->assertSame(1, $result->level);
        $this->assertSame($this->sessionId, $result->sessionId);
        $this->assertSame(70.0, $result->paymentsPosted);
        $this->assertSame(20.0, $result->adjustmentsPosted);
        $this->assertSame(0.0, $result->chargesAdded);
        $this->assertTrue($result->levelClosed);
        $this->assertTrue($result->secondaryQueued);

        $this->assertSame(
            [['70.00', '0.00', '1', 'ICN1'], ['0.00', '0.00', '1', 'ICN1'], ['0.00', '20.00', '1', 'ICN1']],
            $this->activity()
        );
        $this->assertSame(1, $this->closedLevel());
        $this->assertSame(
            [self::SECONDARY, '2'],
            $this->row('SELECT payer_id, payer_type FROM claims WHERE patient_id = ? AND encounter_id = ? ORDER BY version DESC LIMIT 1')
        );

        $this->assertSame(
            [
                'Info: Claim status 1: Processed as Primary',
                'Existing: ' . self::DOS . ' Service Item 100.00 / 100.00',
                'Info: Allowed amount is 80.00',
                'Posted: 2026-10-20 Ins1/' . self::CHECK . ' payment -70.00 / 30.00',
                'Posted: 2: ' . $this->reason('2') . ' 10.00',
                'Posted: 2026-10-19 2: ' . $this->reason('2') . ' -10.00 / 30.00',
                'Posted: 2026-10-19 45: ' . $this->reason('45') . ' -20.00 / 10.00',
                'Info: This claim is now re-queued for secondary paper billing',
            ],
            $this->report($result)
        );
    }

    public function testACheckPostedBeforeIsSkipped(): void
    {
        $this->post($this->claim([$this->paidLine()]));

        // A new run, as when the ERA is posted again.
        $result = $this->post($this->claim([$this->paidLine()]));

        $this->assertSame([ClaimIssue::AlreadyPosted], $result->issues);
        $this->assertSame(0.0, $result->paymentsPosted);
        $this->assertFalse($result->levelClosed);
        $this->assertCount(3, $this->activity());
        $this->assertContains('Error: Already posted from check ' . self::CHECK . '; not posted again', $this->report($result));
    }

    public function testAnUnknownClaimPostsNothing(): void
    {
        $result = $this->post($this->claim([$this->paidLine()], ourClaimId: '99990049-1'));

        $this->assertSame([ClaimIssue::ClaimNotFound], $result->issues);
        $this->assertSame(0, $result->pid);
        $this->assertSame('99990049-1', $result->invoiceNumber);
        $this->assertSame([], $this->activity());
        $this->assertContains('Error: The following claim is not in our database', $this->report($result));
        $this->assertContains('Error: 2026-10-19 CPT4:99213 returned by Ins1 2026-10-19 100.00 / ', $this->report($result));
        $refused = $result->lines[2];
        $this->assertInstanceOf(ReportDetail::class, $refused);
        $this->assertSame('ANNE POSTING', $refused->patientName, "The patient's name from the ERA");
    }

    public function testADenialIsRecordedOnTheClaimAndNotPosted(): void
    {
        $line = new ServiceLine('99213', '', 100.00, 0.0, null, null, [new Adjustment('CO', '204', 100.00)]);

        $result = $this->post($this->claim([$line], status: '4'));

        $this->assertSame([ClaimIssue::Denied], $result->issues);
        $this->assertSame([], $this->activity());
        $this->assertSame(
            ['7', '99213__CO_204'],
            $this->row('SELECT status, process_file FROM claims WHERE patient_id = ? AND encounter_id = ? ORDER BY version DESC LIMIT 1')
        );

        $again = $this->post($this->claim([$line], status: '4'));
        $this->assertContains('Error: Denial already recorded for this claim; not recorded again', $this->report($again));
    }

    public function testAReversalReopensTheLevelItTookBack(): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE form_encounter SET last_level_closed = 1 WHERE pid = ? AND encounter = ?',
            [self::PID, self::ENC]
        );
        $line = new ServiceLine('99213', '', -100.00, -70.0, null, null, []);

        $result = $this->post($this->claim([$line], status: '22'));

        $this->assertSame([ClaimIssue::Reversal], $result->issues);
        $this->assertSame([], $this->activity());
        $this->assertSame(0, $this->closedLevel());
        $this->assertContains('Error: Ins1 re-opened, so the balance stays with insurance', $this->report($result));
    }

    public function testAChargeThatDiffersFromTheInvoiceIsNotPosted(): void
    {
        $line = new ServiceLine('99213', '', 90.00, 60.0, null, null, []);

        $result = $this->post($this->claim([$line]));

        $this->assertSame([ClaimIssue::ChargeMismatch], $result->issues);
        $this->assertSame([], $this->activity());
        $this->assertSame(0, $this->closedLevel());
        $this->assertContains('Error: EOB charge amount 90 for this code does not match our invoice', $this->report($result));
    }

    public function testACodeNotOnTheInvoiceIsRefused(): void
    {
        $line = new ServiceLine('99214', '', 150.00, 0.0, null, null, []);

        $result = $this->post($this->claim([$this->paidLine(), $line]));

        $this->assertSame([ClaimIssue::CodeNotOnInvoice], $result->issues);
        $this->assertCount(3, $this->activity(), 'The lines before it still post');
        $this->assertFalse($result->levelClosed);
    }

    public function testACodeNotOnTheInvoiceIsAddedWhenTheGlobalSaysSo(): void
    {
        $line = new ServiceLine('99214', '25', 150.00, 0.0, null, null, []);

        $result = $this->post($this->claim([$line]), addUnmatchedCodes: true);

        $this->assertSame([], $result->issues);
        $this->assertSame(150.0, $result->chargesAdded);
        $this->assertSame(
            ['99214', '25', '150.00'],
            $this->row("SELECT code, modifier, fee FROM billing WHERE pid = ? AND encounter = ? AND code = '99214'")
        );
        $this->assertContains('Posted: 2026-10-19 CPT4:99214:25 Added by Ins1 2026-10-19 150.00 / 150.00', $this->report($result));
        $this->assertFalse($result->levelClosed, 'The invoice line the ERA left out has no answer yet');
    }

    public function testANonContractualAdjustmentOnAnUnpaidLineIsNotPosted(): void
    {
        $line = new ServiceLine('99213', '', 100.00, 0.0, null, null, [new Adjustment('CO', '97', 100.00)]);

        $result = $this->post($this->claim([$line]));

        $this->assertSame([ClaimIssue::UnpaidAdjustment], $result->issues);
        $this->assertSame([], $this->activity());
        $this->assertContains(
            'Error: 2026-10-19 97: ' . $this->reason('97') . ' -100.00 / ',
            $this->report($result)
        );
    }

    public function testAPendedLineKeepsTheLevelOpen(): void
    {
        $line = new ServiceLine('99213', '', 100.00, 0.0, null, 'N4', [new Adjustment('CO', '133', 100.00)]);

        $result = $this->post($this->claim([$line]));

        $this->assertSame([ClaimIssue::LinesPended], $result->issues);
        $this->assertSame([], $this->activity());
        $this->assertSame(0, $this->closedLevel());
        $report = $this->report($result);
        $this->assertContains('Info: N4: ' . (BillingUtilities::REMITTANCE_ADVICE_REMARK_CODES['N4'] ?? ''), $report);
        $this->assertContains('Error: Ins1 left open: the payer pended 1 line(s) (adjustment reason 133); wait for its final remittance', $report);
    }

    public function testWithoutUpdateReportsButPostsNothing(): void
    {
        $result = $this->post($this->claim([$this->paidLine()]), withoutUpdate: true);

        $this->assertSame([], $result->issues);
        $this->assertSame(0.0, $result->paymentsPosted);
        $this->assertFalse($result->levelClosed);
        $this->assertSame([], $this->activity());
        $this->assertSame(0, $this->closedLevel());
        $this->assertContains(
            'Posted: 2026-10-20 Ins1/' . self::CHECK . ' payment -70.00 / 100.00',
            $this->report($result),
            'The balance stays as listed'
        );
    }

    public function testASecondaryPaymentPostsAdjustmentsAsNotesAndFindsTheModifier(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [self::PID]);
        $this->addCharge('99213', '25', 100.00);
        $line = new ServiceLine('99213', '', 100.00, 10.0, null, null, [new Adjustment('CO', '45', 20.00)]);

        $result = $this->post($this->claim([$line], status: '2', crossover: 1));

        $this->assertSame([], $result->issues);
        $this->assertSame(2, $result->level);
        $this->assertSame(
            [['10.00', '0.00', '2', 'ICN1'], ['0.00', '0.00', '2', 'ICN1']],
            $this->activity()
        );
        $this->assertTrue($result->levelClosed);
        $this->assertFalse($result->secondaryQueued);
        $report = $this->report($result);
        $this->assertContains('Posted: 2026-10-20 Ins2/' . self::CHECK . ' payment -10.00 / 90.00', $report);
        $this->assertContains('Info: This claim is processed by Insurance 2 and automatically forwarded to Insurance 3 for processing. ', $report);
        $this->assertSame(
            ['2', '2'],
            $this->row('SELECT last_level_closed, last_level_billed FROM form_encounter WHERE pid = ? AND encounter = ?')
        );
    }

    public function testALineAnsweredLaterInTheSameRunClosesTheLevel(): void
    {
        // One ERA: the first claim payment pends the line, a later one on the same check answers it.
        $run = new EraCheckPosting(self::PRIMARY);
        $pended = new ServiceLine('99213', '', 100.00, 0.0, null, null, [new Adjustment('OA', '133', 100.00)]);

        $first = $this->post($this->claim([$pended]), run: $run);
        $second = $this->post($this->claim([$this->paidLine()]), run: $run);

        $this->assertSame([ClaimIssue::LinesPended], $first->issues);
        $this->assertFalse($first->levelClosed);
        $this->assertSame([], $second->issues);
        $this->assertSame(70.0, $second->paymentsPosted);
        $this->assertSame(0, $run->pendedLineCount(self::PID, self::ENC, 1));
        $this->assertTrue($second->levelClosed);
        $this->assertSame(1, $this->closedLevel());
    }

    public function testNoSecondaryOnTheEncounterDateOnlyReopensTheClaim(): void
    {
        // The secondary starts after the encounter date but before the ERA's date of
        // service: the page sees a secondary, the queueing (by encounter date) doesn't.
        QueryUtils::sqlStatementThrowException(
            "UPDATE insurance_data SET date = '2026-09-20' WHERE pid = ? AND type = 'secondary'",
            [self::PID]
        );

        $result = $this->post($this->claim([$this->paidLine()], serviceDate: '20260925'));

        $this->assertTrue($result->levelClosed);
        $this->assertFalse($result->secondaryQueued);
        // Reopened with no payer, as SLEOB::arSetupSecondary() has always done.
        $this->assertSame(
            ['0', '-1'],
            $this->row('SELECT payer_id, payer_type FROM claims WHERE patient_id = ? AND encounter_id = ? ORDER BY version DESC LIMIT 1')
        );
    }

    public function testWarningsAndInvoiceLinesTheEraLeftOutAreReported(): void
    {
        $this->addCharge('85025', '', 30.00);

        $result = $this->post($this->claim([$this->paidLine()], warnings: "MOA segment at claim level ignored.\n"));

        $this->assertFalse($result->levelClosed, 'The 85025 line has no answer yet');
        $report = $this->report($result);
        $this->assertContains('Info: MOA segment at claim level ignored.', $report);
        $this->assertContains('Existing: ' . self::DOS . ' Service Item 30.00 / 40.00', $report);
    }

    private function post(
        ClaimPayment $claim,
        bool $withoutUpdate = false,
        bool $addUnmatchedCodes = false,
        ?EraCheckPosting $run = null,
    ): ClaimPostingResult {
        $posting = new ClaimPosting(
            $run ?? new EraCheckPosting(self::PRIMARY),
            new EraReversal(self::PRIMARY),
            new Recorder(),
            1,
            self::PRIMARY,
            $withoutUpdate,
            '',
            $addUnmatchedCodes,
        );
        return $posting->post($claim, $this->sessionId);
    }

    /**
     * @param list<ServiceLine> $lines
     */
    private function claim(
        array $lines,
        string $status = '1',
        string $ourClaimId = self::PID . '-' . self::ENC,
        int $crossover = 0,
        string $warnings = '',
        string $serviceDate = '20260915',
    ): ClaimPayment {
        return new ClaimPayment(
            checkNumber: self::CHECK,
            checkDate: self::CHECK_DATE,
            productionDate: '20261019',
            ourClaimId: $ourClaimId,
            claimStatusCode: $status,
            payerClaimId: 'ICN1',
            serviceDate: $serviceDate,
            patientFirstName: 'ANNE',
            patientLastName: 'POSTING',
            warnings: $warnings,
            crossover: $crossover,
            serviceLines: $lines,
        );
    }

    /**
     * 99213 billed 100, allowed 80: paid 70, coinsurance 10, contractual 20.
     */
    private function paidLine(): ServiceLine
    {
        return new ServiceLine('99213', '', 100.00, 70.0, 80.0, null, [
            new Adjustment('PR', '2', 10.00),
            new Adjustment('CO', '45', 20.00),
        ]);
    }

    /**
     * Each report line as "Style: text" or "Style: date description amount / balance".
     *
     * @return list<string>
     */
    private function report(ClaimPostingResult $result): array
    {
        return array_map(
            static fn(ReportMessage|ReportDetail $line): string => $line instanceof ReportMessage
                ? $line->style->name . ': ' . $line->text
                : $line->style->name . ': ' . $line->date . ' ' . $line->description . ' '
                    . sprintf('%.2f', $line->amount) . ' / '
                    . (is_string($line->balance) ? $line->balance : sprintf('%.2f', $line->balance)),
            $result->lines
        );
    }

    private function addCharge(string $code, string $modifier, float $fee): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO billing (date, code_type, code, pid, provider_id, user, groupname, authorized, "
            . "encounter, code_text, billed, activity, fee, units, modifier) "
            . "VALUES (?, 'CPT4', ?, ?, 1, 1, 'Default', 1, ?, 'test', 1, 1, ?, 1, ?)",
            [self::DOS . ' 00:00:00', $code, self::PID, self::ENC, $fee, $modifier]
        );
    }

    /**
     * @return list<array{string, string, string, string}> pay_amount, adj_amount, payer_type, payer_claim_number
     */
    private function activity(): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT pay_amount, adj_amount, payer_type, payer_claim_number FROM ar_activity WHERE pid = ? AND deleted IS NULL ORDER BY sequence_no',
            [self::PID]
        );
        $activity = [];
        foreach ($rows as $row) {
            $activity[] = [
                self::text($row['pay_amount'] ?? null),
                self::text($row['adj_amount'] ?? null),
                self::text($row['payer_type'] ?? null),
                self::text($row['payer_claim_number'] ?? null),
            ];
        }
        return $activity;
    }

    /**
     * The first row of a query on the test claim, its values as strings.
     *
     * @return list<string>
     */
    private function row(string $sql): array
    {
        $row = QueryUtils::querySingleRow($sql, [self::PID, self::ENC]);
        return array_values(array_map(
            self::text(...),
            is_array($row) ? $row : []
        ));
    }

    private function reason(string $code): string
    {
        return BillingUtilities::CLAIM_ADJUSTMENT_REASON_CODES[$code] ?? '';
    }

    private static function text(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    private function closedLevel(): int
    {
        $closed = QueryUtils::fetchSingleValue(
            'SELECT last_level_closed FROM form_encounter WHERE pid = ? AND encounter = ?',
            'last_level_closed',
            [self::PID, self::ENC]
        );
        return is_numeric($closed) ? (int) $closed : -1;
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException("DELETE FROM ar_session WHERE reference = ?", ['ePay - ' . self::CHECK]);
        QueryUtils::sqlStatementThrowException('DELETE FROM claims WHERE patient_id = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
    }
}
