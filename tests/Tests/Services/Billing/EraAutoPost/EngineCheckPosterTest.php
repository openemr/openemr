<?php

/**
 * Database tests for EngineCheckPoster: the payer a check is posted for, and posting a check to its invoices.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing\EraAutoPost;

use OpenEMR\Billing\EraAutoPost\AutoPostRepository;
use OpenEMR\Billing\EraAutoPost\CheckStatus;
use OpenEMR\Billing\EraAutoPost\ClaimOutcome;
use OpenEMR\Billing\EraAutoPost\EngineCheckPoster;
use OpenEMR\Billing\EraAutoPost\EraAutoPoster;
use OpenEMR\Billing\EraAutoPost\RemittanceCheck;
use OpenEMR\Billing\EraAutoPost\RemittanceReader;
use OpenEMR\Billing\EraPosting\Adjustment;
use OpenEMR\Billing\EraPosting\ClaimPayment;
use OpenEMR\Billing\EraPosting\ServiceLine;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;

class EngineCheckPosterTest extends TestCase
{
    /** Synthetic ids, clear of the demo data. */
    private const PID = 99990051;
    private const ENC = 99990151;
    private const PAYER_A = 99990351;
    private const PAYER_B = 99990352;
    private const POST_USER = 99990451;
    private const PARTNER = 99990071;
    private const DOS = '2026-09-15';
    private const CHECK = 'AUTOCHK1';
    private const CHECK_DATE = '20261020';

    protected function setUp(): void
    {
        $this->cleanUp();
        // Three patients, one encounter each: two insured by payer A, one by payer B.
        foreach ([self::PAYER_A, self::PAYER_A, self::PAYER_B] as $i => $payer) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'Anne', 'Posting', NOW())",
                [self::PID + $i, random_bytes(16)]
            );
            QueryUtils::sqlInsert(
                'INSERT INTO form_encounter (pid, encounter, date, last_level_billed, last_level_closed) VALUES (?, ?, ?, 1, 0)',
                [self::PID + $i, self::ENC + $i, self::DOS . ' 00:00:00']
            );
            QueryUtils::sqlInsert(
                "INSERT INTO insurance_data (pid, type, provider, date) VALUES (?, 'primary', ?, '2026-01-01')",
                [self::PID + $i, (string) $payer]
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO billing (date, code_type, code, pid, provider_id, user, groupname, authorized, "
                . "encounter, code_text, billed, activity, fee, units, modifier) "
                . "VALUES (?, 'CPT4', '99213', ?, 1, 1, 'Default', 1, ?, 'test', 1, 1, 100.00, 1, '')",
                [self::DOS . ' 00:00:00', self::PID + $i, self::ENC + $i]
            );
        }
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testThePayerMostClaimsHaveIsChosen(): void
    {
        $poster = new EngineCheckPoster(false);

        $this->assertSame((string) self::PAYER_A, $poster->payerFor($this->check([$this->claim(0), $this->claim(1), $this->claim(2)])));
        // One claim each: the lower id, whatever the order in the file.
        $this->assertSame((string) self::PAYER_A, $poster->payerFor($this->check([$this->claim(2), $this->claim(0)])));
        $this->assertSame((string) self::PAYER_B, $poster->payerFor($this->check([$this->claim(2)])));
    }

    public function testClaimsThatLeadToNoPayerDoNotCount(): void
    {
        $poster = new EngineCheckPoster(false);

        // Not in the database; no secondary insurance on file; a reversal.
        $this->assertSame('', $poster->payerFor($this->check([$this->claim(0, ourClaimId: '99990059-1')])));
        $this->assertSame('', $poster->payerFor($this->check([$this->claim(0, status: '2')])));
        $this->assertSame('', $poster->payerFor($this->check([$this->claim(0, status: '22')])));
        $this->assertSame((string) self::PAYER_B, $poster->payerFor($this->check([$this->claim(0, status: '22'), $this->claim(2)])));
    }

    public function testAClaimWithNoServiceDateUsesTheEncountersDate(): void
    {
        $poster = new EngineCheckPoster(false);

        $this->assertSame((string) self::PAYER_A, $poster->payerFor($this->check([$this->claim(0, serviceDate: '')])));
    }

    public function testPostingCreatesTheSessionForTheUserAndPostsEachClaim(): void
    {
        $poster = new EngineCheckPoster(false);
        $check = $this->check([$this->claim(0), $this->claim(1, ourClaimId: '99990059-1')]);

        $record = $poster->post($check, (string) self::PAYER_A, self::POST_USER, new \DateTimeImmutable('2026-11-19 12:00:00'));

        $this->assertSame(CheckStatus::Posted, $record->status);
        $this->assertNull($record->holdReason);
        $this->assertSame((string) self::PAYER_A, $record->payerId);
        $this->assertSame(
            [
                'payer_id' => (string) self::PAYER_A,
                'user_id' => (string) self::POST_USER,
                'reference' => 'ePay - ' . self::CHECK,
                'check_date' => '2026-10-20',
                'pay_total' => '140.00',
                'post_to_date' => '2026-11-19',
                'deposit_date' => '2026-11-19',
                'payment_type' => 'insurance',
                'adjustment_code' => 'insurance_payment',
                'payment_method' => 'electronic',
            ],
            array_map(
                static fn(mixed $value): string => is_scalar($value) ? (string) $value : '',
                QueryUtils::querySingleRow(
                    'SELECT payer_id, user_id, reference, check_date, pay_total, post_to_date, deposit_date, payment_type,'
                    . ' adjustment_code, payment_method FROM ar_session WHERE session_id = ?',
                    [$record->sessionId]
                ) ?: []
            )
        );

        [$posted, $unknown] = $record->claims;
        $this->assertSame(ClaimOutcome::Posted, $posted->outcome);
        $this->assertSame([], $posted->reasons);
        $this->assertSame(self::PID, $posted->pid);
        $this->assertSame(self::ENC, $posted->encounter);
        $this->assertSame(70.0, $posted->paymentsPosted);
        $this->assertSame(20.0, $posted->adjustmentsPosted);
        $this->assertTrue($posted->levelClosed);
        $this->assertSame(ClaimOutcome::Review, $unknown->outcome);
        $this->assertSame(['claim_not_found'], $unknown->reasons);

        $this->assertSame(
            [['70.00', '0.00', (string) self::POST_USER], ['0.00', '0.00', (string) self::POST_USER], ['0.00', '20.00', (string) self::POST_USER]],
            $this->activity(self::PID, $record->sessionId)
        );
    }

    public function testPostingTheCheckAgainReusesItsSessionAndSkipsItsClaims(): void
    {
        $poster = new EngineCheckPoster(false);
        $check = $this->check([$this->claim(0)]);
        $first = $poster->post($check, (string) self::PAYER_A, self::POST_USER, new \DateTimeImmutable('2026-11-19'));

        $second = $poster->post($check, (string) self::PAYER_A, self::POST_USER, new \DateTimeImmutable('2026-11-20'));

        $this->assertSame($first->sessionId, $second->sessionId);
        $this->assertSame(ClaimOutcome::Skipped, $second->claims[0]->outcome);
        $this->assertSame(['already_posted'], $second->claims[0]->reasons);
        $this->assertCount(3, $this->activity(self::PID, $first->sessionId));
        $this->assertEquals(1, QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM ar_session WHERE reference = ?', 'n', ['ePay - ' . self::CHECK]));
    }

    public function testACheckPostedByHandForAnotherPayerIsSeen(): void
    {
        $poster = new EngineCheckPoster(false);
        $check = $this->check([$this->claim(0)]);
        $this->assertFalse($poster->postedForAnotherPayer($check, (string) self::PAYER_A));

        // Manual posting stores the bare check number.
        QueryUtils::sqlInsert(
            'INSERT INTO ar_session (payer_id, user_id, reference, check_date, deposit_date, pay_total, modified_time,'
            . " global_amount, payment_type, adjustment_code, post_to_date, patient_id, payment_method)"
            . " VALUES (?, 1, ?, ?, ?, 70, NOW(), 0, 'insurance', 'insurance_payment', ?, 0, 'electronic')",
            [self::PAYER_B, self::CHECK, self::CHECK_DATE, self::CHECK_DATE, self::CHECK_DATE]
        );

        $this->assertTrue($poster->postedForAnotherPayer($check, (string) self::PAYER_A));
        $this->assertFalse($poster->postedForAnotherPayer($check, (string) self::PAYER_B));
        // The same number on another date is another check.
        $other = new RemittanceCheck(self::CHECK, '20250101', 70.0, 'TEST PAYER', [$this->claim(0)], 0.0, []);
        $this->assertFalse($poster->postedForAnotherPayer($other, (string) self::PAYER_A));
    }

    public function testAReceivedFileIsPostedOnceByTheWholeRun(): void
    {
        $directory = sys_get_temp_dir() . '/era_run_' . bin2hex(random_bytes(6));
        mkdir($directory);
        $claimId = self::PID . '-' . self::ENC;
        file_put_contents(
            $directory . '/run.edi',
            "ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *261020*1200*^*00501*000000042*0*P*:~"
            . "GS*HP*PAYER*RECEIVER*20261020*1200*1*X*005010X221A1~"
            . "ST*835*0001~BPR*I*70.00*C*ACH************" . self::CHECK_DATE . "~TRN*1*" . self::CHECK . "*1234567890~"
            . "DTM*405*20261019~N1*PR*TEST PAYER~N1*PE*TEST PROVIDER*XX*1234567890~LX*1~"
            . "CLP*$claimId*1*100.00*70.00*10.00*MC*ICN0~NM1*QC*1*POSTING*ANNE~"
            . "SVC*HC:99213*100.00*70.00~DTM*472*20260915~CAS*PR*2*10.00~CAS*CO*45*20.00~AMT*B6*80.00~"
            . "SE*15*0001~GE*1*1~IEA*1*000000042~"
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `users` (`id`, `uuid`, `username`, `fname`, `lname`, `active`) VALUES (?, ?, 'autopostrun', 'Auto', 'Poster', 1)",
            [self::POST_USER, random_bytes(16)]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `x12_partners` (`id`, `name`, `x12_sftp_host`, `x12_sftp_receive_dir_835`, `x12_era_auto_post_user`)"
            . " VALUES (?, 'auto post run test', 'sftp.example.test', '/remits', ?)",
            [self::PARTNER, self::POST_USER]
        );
        $receivedId = QueryUtils::sqlInsert(
            'INSERT INTO `x12_remote_received` (`remote_key`, `x12_partner_id`, `remote_dir`, `remote_name`, `remote_size`,'
            . " `file_type`, `status`, `stored_name`, `received_at`, `auto_post_status`)"
            . " VALUES (?, ?, '/remits', 'run.edi', 1, 'f835', 'stored', 'run.edi', ?, 'pending')",
            [hash('sha256', 'auto post run test'), self::PARTNER, '2026-11-19 12:00:00']
        );
        $clock = new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('2026-11-19 12:00:05');
            }
        };
        $autoPoster = new EraAutoPoster(
            new AutoPostRepository(),
            new RemittanceReader(),
            new EngineCheckPoster(false),
            $clock,
            new NullLogger(),
            $directory,
        );

        try {
            $first = $autoPoster->run();
            $second = $autoPoster->run();
        } finally {
            unlink($directory . '/run.edi');
            rmdir($directory);
        }

        $this->assertSame(1, $first->files);
        $this->assertSame(1, $first->checksPosted);
        $this->assertSame(1, $first->claimsPosted);
        // The file is not taken a second time: nothing more is posted or recorded.
        $this->assertSame(0, $second->files);
        $row = QueryUtils::querySingleRow(
            'SELECT k.`status`, k.`payer_id`, k.`session_id`, c.`outcome`, c.`pid`, c.`encounter`, c.`payments_posted`,'
            . ' c.`adjustments_posted`, c.`level_closed`, r.`auto_post_status` FROM `era_auto_post_check` AS k'
            . ' JOIN `era_auto_post_claim` AS c ON c.`check_id` = k.`id`'
            . ' JOIN `x12_remote_received` AS r ON r.`id` = k.`received_id` WHERE k.`received_id` = ?',
            [$receivedId]
        ) ?: [];
        $sessionId = is_numeric($row['session_id'] ?? null) ? (int) $row['session_id'] : 0;
        unset($row['session_id']);
        $this->assertSame(
            [
                'status' => 'posted', 'payer_id' => (string) self::PAYER_A, 'outcome' => 'posted', 'pid' => (string) self::PID,
                'encounter' => (string) self::ENC, 'payments_posted' => '70.00', 'adjustments_posted' => '20.00',
                'level_closed' => '1', 'auto_post_status' => 'done',
            ],
            array_map(static fn(mixed $value): string => is_scalar($value) ? (string) $value : '', $row)
        );
        $this->assertCount(3, $this->activity(self::PID, $sessionId));
        $this->assertEquals(1, QueryUtils::fetchSingleValue('SELECT COUNT(*) AS n FROM `era_auto_post_check` WHERE `received_id` = ?', 'n', [$receivedId]));
    }

    /**
     * @param list<ClaimPayment> $claims
     */
    private function check(array $claims): RemittanceCheck
    {
        return new RemittanceCheck(self::CHECK, self::CHECK_DATE, 140.0, 'TEST PAYER', $claims, 0.0, []);
    }

    /**
     * Patient $i's 99213 billed 100, allowed 80: paid 70, coinsurance 10, contractual 20.
     */
    private function claim(int $i, string $status = '1', ?string $ourClaimId = null, string $serviceDate = '20260915'): ClaimPayment
    {
        return new ClaimPayment(
            checkNumber: self::CHECK,
            checkDate: self::CHECK_DATE,
            productionDate: '20261019',
            ourClaimId: $ourClaimId ?? (self::PID + $i) . '-' . (self::ENC + $i),
            claimStatusCode: $status,
            payerClaimId: 'ICN' . $i,
            serviceDate: $serviceDate,
            patientFirstName: 'ANNE',
            patientLastName: 'POSTING',
            warnings: '',
            crossover: 0,
            serviceLines: [
                new ServiceLine('99213', '', 100.00, 70.0, 80.0, null, [
                    new Adjustment('PR', '2', 10.00),
                    new Adjustment('CO', '45', 20.00),
                ]),
            ],
            paymentAmount: 70.0,
        );
    }

    /**
     * @return list<array{string, string, string}> pay_amount, adj_amount, post_user
     */
    private function activity(int $pid, int $sessionId): array
    {
        $rows = QueryUtils::fetchRecords(
            'SELECT pay_amount, adj_amount, post_user FROM ar_activity WHERE pid = ? AND session_id = ? AND deleted IS NULL ORDER BY sequence_no',
            [$pid, $sessionId]
        );
        $text = static fn(mixed $value): string => is_scalar($value) ? (string) $value : '';
        return array_map(
            static fn(array $row): array => [$text($row['pay_amount'] ?? ''), $text($row['adj_amount'] ?? ''), $text($row['post_user'] ?? '')],
            $rows
        );
    }

    private function cleanUp(): void
    {
        foreach ([self::PID, self::PID + 1, self::PID + 2] as $pid) {
            QueryUtils::sqlStatementThrowException('DELETE FROM ar_activity WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM claims WHERE patient_id = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ?', [$pid]);
            QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [$pid]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM ar_session WHERE reference IN (?, ?)', [self::CHECK, 'ePay - ' . self::CHECK]);
        QueryUtils::sqlStatementThrowException(
            'DELETE c FROM `era_auto_post_claim` AS c JOIN `era_auto_post_check` AS k ON k.`id` = c.`check_id`'
            . ' JOIN `x12_remote_received` AS r ON r.`id` = k.`received_id` WHERE r.`x12_partner_id` = ?',
            [self::PARTNER]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE k FROM `era_auto_post_check` AS k JOIN `x12_remote_received` AS r ON r.`id` = k.`received_id` WHERE r.`x12_partner_id` = ?',
            [self::PARTNER]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `x12_remote_received` WHERE `x12_partner_id` = ?', [self::PARTNER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `x12_partners` WHERE `id` = ?', [self::PARTNER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `users` WHERE `id` = ?', [self::POST_USER]);
    }
}
