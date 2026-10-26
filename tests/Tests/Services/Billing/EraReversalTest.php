<?php

/**
 * Database tests for EraReversal: a payment reversal in an ERA re-opens the
 * insurance level its payment closed, unless the same check also posted a
 * corrected payment for the claim.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\EraReversal;
use OpenEMR\Billing\EraReversalOutcome;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class EraReversalTest extends TestCase
{
    /** Synthetic ids, clear of the demo data. */
    private const PID = 99990031;
    private const ENC = 99990131;
    private const PRIMARY = '99990331';
    private const SECONDARY = '99990332';
    private const DOS = '2026-09-15';
    private const CHECK = 'REVCHK1';
    private const CHECK_DATE = '20261020';

    protected function setUp(): void
    {
        $this->cleanUp();
        foreach ([['primary', self::PRIMARY], ['secondary', self::SECONDARY]] as [$type, $provider]) {
            QueryUtils::sqlInsert(
                'INSERT INTO insurance_data (pid, type, provider, date) VALUES (?, ?, ?, ?)',
                [self::PID, $type, $provider, '2026-01-01']
            );
        }
        QueryUtils::sqlInsert(
            'INSERT INTO form_encounter (pid, encounter, date, last_level_billed, last_level_closed) VALUES (?, ?, ?, 1, 1)',
            [self::PID, self::ENC, self::DOS]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testWithAPayerChosenTheLevelIsThatPayersLevel(): void
    {
        $this->assertSame(1, $this->level(self::PRIMARY));
        $this->assertSame(2, $this->level(self::SECONDARY));
        $this->assertSame(0, $this->level('99990339'), 'A payer not on the claim');
        $this->assertSame(
            0,
            (new EraReversal(self::PRIMARY))->reversedLevel(self::PID, self::ENC, '2025-12-31', ''),
            'Not in effect before the policy starts'
        );
    }

    public function testWithoutAPayerTheReversedPaymentGivesTheLevel(): void
    {
        // EOB Posting - Search opens the ERA without choosing a payer (no InsId).
        $this->postFromCheck('0', 2, '30.00', 'ORIGCHK', 'ICNSEC1');
        $this->setClosedLevel(2);

        $level = (new EraReversal(''))->reversedLevel(self::PID, self::ENC, self::DOS, 'ICNSEC1');
        $this->assertSame(2, $level);
        $this->assertSame(EraReversalOutcome::Reopened, (new EraReversal(''))->reopenLevel(self::PID, self::ENC, $level, self::CHECK, self::CHECK_DATE));
        $this->assertSame(1, $this->closedLevel());
    }

    public function testWithoutAPayerOneInsuranceGivesLevelOne(): void
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM insurance_data WHERE pid = ? AND type = 'secondary'", [self::PID]);

        $this->assertSame(1, (new EraReversal(''))->reversedLevel(self::PID, self::ENC, self::DOS, 'NOMATCH'));
    }

    public function testWithoutAPayerOrAClueTheLevelIsUnknown(): void
    {
        $reversal = new EraReversal('');
        $level = $reversal->reversedLevel(self::PID, self::ENC, self::DOS, 'NOMATCH');

        $this->assertSame(0, $level);
        $this->assertSame(EraReversalOutcome::LevelUnknown, $reversal->reopenLevel(self::PID, self::ENC, $level, self::CHECK, self::CHECK_DATE));
        $this->assertSame(1, $this->closedLevel());
    }

    public function testWithoutAPayerACorrectionFromTheSameCheckStillCounts(): void
    {
        // Sessions created without a payer chosen have payer_id 0.
        $this->postFromCheck('0', 1, '80.00');

        $this->assertSame(EraReversalOutcome::CorrectionPosted, (new EraReversal(''))->reopenLevel(self::PID, self::ENC, 1, self::CHECK, self::CHECK_DATE));
        $this->assertSame(1, $this->closedLevel());
    }

    public function testAReversalAloneReopensTheLevel(): void
    {
        $this->assertSame(EraReversalOutcome::Reopened, $this->reverse(self::PRIMARY, 1));
        $this->assertSame(0, $this->closedLevel());
    }

    public function testACorrectedPaymentFromTheSameCheckKeepsTheLevelClosed(): void
    {
        $this->postFromCheck(self::PRIMARY, 1, '80.00');

        $this->assertSame(EraReversalOutcome::CorrectionPosted, $this->reverse(self::PRIMARY, 1));
        $this->assertSame(1, $this->closedLevel());
    }

    public function testAZeroPaidCorrectionStillCounts(): void
    {
        // A corrected claim that paid nothing (all deductible) posts notes only.
        $this->postFromCheck(self::PRIMARY, 1, '0.00');

        $this->assertSame(EraReversalOutcome::CorrectionPosted, $this->reverse(self::PRIMARY, 1));
        $this->assertSame(1, $this->closedLevel());
    }

    public function testAPaymentFromAnotherCheckIsTheOneReversed(): void
    {
        $this->postFromCheck(self::PRIMARY, 1, '80.00', 'ORIGCHK');

        $this->assertSame(EraReversalOutcome::Reopened, $this->reverse(self::PRIMARY, 1));
        $this->assertSame(0, $this->closedLevel());
    }

    public function testAnOpenLevelIsLeftAlone(): void
    {
        $this->setClosedLevel(0);

        $this->assertSame(EraReversalOutcome::AlreadyOpen, $this->reverse(self::PRIMARY, 1));
        $this->assertSame(0, $this->closedLevel());
    }

    public function testASecondaryReversalReopensOnlyTheSecondary(): void
    {
        $this->setClosedLevel(2);

        $this->assertSame(EraReversalOutcome::Reopened, $this->reverse(self::SECONDARY, 2));
        $this->assertSame(1, $this->closedLevel());
    }

    public function testAPrimaryReversalAfterTheSecondaryClosedReopensBoth(): void
    {
        $this->setClosedLevel(2);

        $this->assertSame(EraReversalOutcome::Reopened, $this->reverse(self::PRIMARY, 1));
        $this->assertSame(0, $this->closedLevel());
    }

    public function testAPayerNotOnTheClaimChangesNothing(): void
    {
        $this->assertSame(EraReversalOutcome::PayerNotOnClaim, $this->reverse('99990339', $this->level('99990339')));
        $this->assertSame(1, $this->closedLevel());
    }

    private function level(string $payer): int
    {
        return (new EraReversal($payer))->reversedLevel(self::PID, self::ENC, self::DOS, '');
    }

    private function reverse(string $payer, int $level): EraReversalOutcome
    {
        return (new EraReversal($payer))->reopenLevel(self::PID, self::ENC, $level, self::CHECK, self::CHECK_DATE);
    }

    /**
     * What ERA posting writes for a claim paid from a check: a session for the
     * check and the claim's lines under it.
     */
    private function postFromCheck(string $payer, int $level, string $paid, string $check = self::CHECK, ?string $payerClaimNumber = null): void
    {
        $session = QueryUtils::sqlInsert(
            'INSERT INTO ar_session (payer_id, user_id, reference, check_date, deposit_date, pay_total, modified_time,'
            . " global_amount, payment_type, adjustment_code, post_to_date, patient_id, payment_method)"
            . " VALUES (?, 1, ?, ?, ?, ?, NOW(), 0, 'insurance', 'insurance_payment', ?, 0, 'electronic')",
            [$payer, 'ePay - ' . $check, self::CHECK_DATE, self::CHECK_DATE, $paid, self::CHECK_DATE]
        );
        QueryUtils::sqlInsert(
            'INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_user,'
            . " session_id, memo, pay_amount, adj_amount, modified_time, follow_up, account_code, payer_claim_number)"
            . " VALUES (?, ?, 1, 'CPT4', '99213', '', ?, NOW(), 1, ?, 'Ins1 dedbl: 20.00', ?, 0, NOW(), '', 'IPP', ?)",
            [self::PID, self::ENC, $level, $session, $paid, $payerClaimNumber]
        );
    }

    private function setClosedLevel(int $level): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE form_encounter SET last_level_closed = ? WHERE pid = ? AND encounter = ?',
            [$level, self::PID, self::ENC]
        );
    }

    private function closedLevel(): int
    {
        $level = QueryUtils::fetchSingleValue(
            'SELECT last_level_closed FROM form_encounter WHERE pid = ? AND encounter = ?',
            'last_level_closed',
            [self::PID, self::ENC]
        );
        $this->assertIsNumeric($level);
        return (int) $level;
    }

    private function cleanUp(): void
    {
        $sessions = QueryUtils::fetchTableColumn(
            'SELECT session_id FROM ar_activity WHERE pid = ? AND encounter = ?',
            'session_id',
            [self::PID, self::ENC]
        );
        foreach ($sessions as $session) {
            QueryUtils::sqlStatementThrowException('DELETE FROM ar_session WHERE session_id = ?', [$session]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE pid = ? AND encounter = ?', [self::PID, self::ENC]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ? AND encounter = ?', [self::PID, self::ENC]);
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
    }
}
