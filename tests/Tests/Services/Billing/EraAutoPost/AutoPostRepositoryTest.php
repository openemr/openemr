<?php

/**
 * Database tests for AutoPostRepository: which received 835s wait for a run, taking one once, and the stored rows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing\EraAutoPost;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Billing\EraAutoPost\AutoPostRepository;
use OpenEMR\Billing\EraAutoPost\CheckRecord;
use OpenEMR\Billing\EraAutoPost\CheckStatus;
use OpenEMR\Billing\EraAutoPost\ClaimOutcome;
use OpenEMR\Billing\EraAutoPost\ClaimRecord;
use OpenEMR\Billing\EraAutoPost\HoldReason;
use OpenEMR\Billing\EraAutoPost\PendingRemittance;
use OpenEMR\Billing\EraAutoPost\RemittanceCheck;
use OpenEMR\Billing\EraPosting\ClaimPayment;
use OpenEMR\Billing\X12Receive\ReceivedFileRepository;
use OpenEMR\Billing\X12Receive\ReceivedStatus;
use OpenEMR\Billing\X12Receive\ReceivePartnerRepository;
use OpenEMR\Billing\X12Receive\RemoteFile;
use OpenEMR\Billing\X12Receive\StoreOutcome;
use OpenEMR\Billing\X12Receive\X12FileType;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class AutoPostRepositoryTest extends TestCase
{
    /** Synthetic ids, clear of the demo data. */
    private const PARTNER_ON = 99990061;
    private const PARTNER_OFF = 99990062;
    private const PARTNER_INACTIVE_USER = 99990063;
    private const USER = 99990461;
    private const INACTIVE_USER = 99990462;
    private const RECEIVED = '2026-11-19 10:00:00';

    protected function setUp(): void
    {
        $this->cleanUp();
        foreach ([[self::USER, 1], [self::INACTIVE_USER, 0]] as [$id, $active]) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `users` (`id`, `uuid`, `username`, `fname`, `lname`, `active`) VALUES (?, ?, ?, 'Auto', 'Poster', ?)",
                [$id, random_bytes(16), 'autopost' . $id, $active]
            );
        }
        foreach ([[self::PARTNER_ON, self::USER], [self::PARTNER_OFF, null], [self::PARTNER_INACTIVE_USER, self::INACTIVE_USER]] as [$id, $user]) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `x12_partners` (`id`, `name`, `x12_sftp_host`, `x12_sftp_receive_dir_835`, `x12_era_auto_post_user`)"
                . " VALUES (?, 'auto post test', 'sftp.example.test', '/remits', ?)",
                [$id, $user]
            );
        }
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testOnlyMarked835sOfAPartnerWithAnActiveUserArePending(): void
    {
        $wanted = $this->received(self::PARTNER_ON, 'wanted.edi');
        // Received before the partner had a user to post as.
        $this->received(self::PARTNER_ON, 'older.edi', autoPostStatus: '');
        $this->received(self::PARTNER_ON, 'status.277', type: 'f277');
        $this->received(self::PARTNER_ON, 'dup.edi', status: 'duplicate');
        $this->received(self::PARTNER_ON, 'taken.edi', autoPostStatus: 'done');
        // Marked when received, but the partner's user has since been taken off or made inactive.
        $this->received(self::PARTNER_OFF, 'off.edi');
        $this->received(self::PARTNER_INACTIVE_USER, 'inactive.edi');

        $pending = $this->ours((new AutoPostRepository())->pending());

        $this->assertCount(1, $pending);
        $this->assertSame($wanted, $pending[0]->receivedId);
        $this->assertSame('wanted.edi', $pending[0]->storedName);
        $this->assertSame(self::USER, $pending[0]->postUser);
    }

    public function testAFileIsMarkedForUnattendedPostingWhenItIsRecorded(): void
    {
        $received = new ReceivedFileRepository();
        $stored = new StoreOutcome(ReceivedStatus::Stored, 'marked.edi');
        $marked = new RemoteFile('marked.RMT', 10, 1700000000);
        $plain = new RemoteFile('plain.RMT', 10, 1700000000);
        $at = new \DateTimeImmutable(self::RECEIVED);

        $received->record(self::PARTNER_ON, '/remits', $marked, X12FileType::Remittance835, $stored, $at, true);
        $received->record(self::PARTNER_ON, '/remits', $plain, X12FileType::Remittance835, new StoreOutcome(ReceivedStatus::Stored, 'plain.edi'), $at);

        $log = new AutoPostRepository();
        $pending = $this->ours($log->pending());
        $this->assertSame(['marked.edi'], array_map(static fn(PendingRemittance $file): string => $file->storedName, $pending));

        // Recording the file again does not put a file a run has taken back in the queue.
        $this->assertTrue($log->take($pending[0]));
        $received->record(self::PARTNER_ON, '/remits', $marked, X12FileType::Remittance835, $stored, $at, true);
        $this->assertSame('running', $this->autoPostStatus($pending[0]->receivedId));
    }

    public function testAFileIsTakenOnceAndNotPendingAgain(): void
    {
        $log = new AutoPostRepository();
        $id = $this->received(self::PARTNER_ON, 'a.edi');

        $file = new PendingRemittance($id, 'a.edi', self::USER);
        $this->assertTrue($log->take($file));
        $this->assertFalse($log->take($file));
        $this->assertSame('running', $this->autoPostStatus($id));
        $this->assertSame([], $this->ours($log->pending()));

        $log->finish($id, AutoPostFileStatus::Done);

        $this->assertSame('done', $this->autoPostStatus($id));
        $this->assertFalse($log->take($file));
        $this->assertSame([], $this->ours($log->pending()));
    }

    public function testAFileIsNotTakenOnceItsPartnersUserIsInactiveOrChanged(): void
    {
        $log = new AutoPostRepository();
        $id = $this->received(self::PARTNER_ON, 'a.edi');
        $file = new PendingRemittance($id, 'a.edi', self::USER);

        // The partner now posts as someone else.
        $this->assertFalse($log->take(new PendingRemittance($id, 'a.edi', self::INACTIVE_USER)));
        // The user was made inactive after the run listed the file.
        QueryUtils::sqlStatementThrowException('UPDATE `users` SET `active` = 0 WHERE `id` = ?', [self::USER]);
        $this->assertFalse($log->take($file));
        $this->assertSame('pending', $this->autoPostStatus($id));

        QueryUtils::sqlStatementThrowException('UPDATE `users` SET `active` = 1 WHERE `id` = ?', [self::USER]);
        $this->assertTrue($log->take($file));
    }

    public function testAStoredFileStaysStoredAndPendingWhenALaterRunReportsADuplicateOrAFailure(): void
    {
        $received = new ReceivedFileRepository();
        $file = new RemoteFile('overlap.RMT', 10, 1700000000);
        $at = new \DateTimeImmutable(self::RECEIVED);
        $received->record(self::PARTNER_ON, '/remits', $file, X12FileType::Remittance835, new StoreOutcome(ReceivedStatus::Stored, 'overlap.edi'), $at, true);

        $received->record(
            self::PARTNER_ON,
            '/remits',
            $file,
            X12FileType::Remittance835,
            new StoreOutcome(ReceivedStatus::Duplicate, 'overlap.edi', 'This file is already stored'),
            $at,
        );

        // Or its download fails, which reports no kind of file at all.
        $received->record(self::PARTNER_ON, '/remits', $file, null, new StoreOutcome(ReceivedStatus::Failed, message: 'The download did not complete'), $at);

        $this->assertSame(
            ['status' => 'stored', 'file_type' => 'f835', 'stored_name' => 'overlap.edi', 'auto_post_status' => 'pending'],
            $this->strings(QueryUtils::querySingleRow(
                'SELECT `status`, `file_type`, `stored_name`, `auto_post_status` FROM `x12_remote_received` WHERE `x12_partner_id` = ? AND `remote_name` = ?',
                [self::PARTNER_ON, 'overlap.RMT']
            ) ?: [])
        );
        $this->assertSame(['overlap.edi'], array_map(static fn(PendingRemittance $f): string => $f->storedName, $this->ours((new AutoPostRepository())->pending())));
    }

    public function testAPostedCheckIsStoredWithARowPerClaimPayment(): void
    {
        $id = $this->received(self::PARTNER_ON, 'a.edi');
        $posted = $this->claim('41-141', '1', 70.0);
        $denied = $this->claim('42-142', '4', 0.0);
        $check = new RemittanceCheck('CHK1', '20261020', 60.0, 'TEST PAYER', [$posted, $denied], 10.0, ['WO', 'L6']);
        $record = new CheckRecord($check, CheckStatus::Posted, null, '77', 300, [
            new ClaimRecord($posted, ClaimOutcome::Posted, [], 41, 141, 1, 70.0, 20.0, 5.0, true, true),
            new ClaimRecord($denied, ClaimOutcome::Review, ['denied', 'lines_pended'], 42, 142, 1),
        ]);

        (new AutoPostRepository())->record($id, 2, $record, new \DateTimeImmutable('2026-11-19 12:00:00'));

        $checkRow = $this->strings(QueryUtils::querySingleRow('SELECT * FROM `era_auto_post_check` WHERE `received_id` = ?', [$id]) ?: []);
        $checkId = $checkRow['id'] ?? '';
        unset($checkRow['id']);
        $this->assertSame(
            [
                'received_id' => (string) $id,
                'sequence' => '2',
                'check_number' => 'CHK1',
                'check_date' => '20261020',
                'payer_name' => 'TEST PAYER',
                'payer_id' => '77',
                'check_amount' => '60.00',
                'claim_total' => '70.00',
                'plb_total' => '10.00',
                'plb_reasons' => 'WO,L6',
                'status' => 'posted',
                'hold_reason' => '',
                'session_id' => '300',
                'processed_at' => '2026-11-19 12:00:00',
            ],
            $checkRow
        );
        $claimRows = array_map(
            $this->strings(...),
            QueryUtils::fetchRecords(
                'SELECT `sequence`, `our_claim_id`, `payer_claim_id`, `patient_name`, `pid`, `encounter`, `payer_level`,'
                . ' `claim_status_code`, `outcome`, `reasons`, `claim_payment`, `payments_posted`, `adjustments_posted`,'
                . ' `charges_added`, `level_closed`, `secondary_queued` FROM `era_auto_post_claim` WHERE `check_id` = ? ORDER BY `sequence`',
                [$checkId]
            )
        );
        $this->assertSame(
            [
                [
                    'sequence' => '1', 'our_claim_id' => '41-141', 'payer_claim_id' => 'ICN41-141', 'patient_name' => 'ANNE POSTING',
                    'pid' => '41', 'encounter' => '141', 'payer_level' => '1', 'claim_status_code' => '1', 'outcome' => 'posted',
                    'reasons' => '', 'claim_payment' => '70.00', 'payments_posted' => '70.00', 'adjustments_posted' => '20.00',
                    'charges_added' => '5.00', 'level_closed' => '1', 'secondary_queued' => '1',
                ],
                [
                    'sequence' => '2', 'our_claim_id' => '42-142', 'payer_claim_id' => 'ICN42-142', 'patient_name' => 'ANNE POSTING',
                    'pid' => '42', 'encounter' => '142', 'payer_level' => '1', 'claim_status_code' => '4', 'outcome' => 'review',
                    'reasons' => 'denied,lines_pended', 'claim_payment' => '0.00', 'payments_posted' => '0.00',
                    'adjustments_posted' => '0.00', 'charges_added' => '0.00', 'level_closed' => '0', 'secondary_queued' => '0',
                ],
            ],
            $claimRows
        );
    }

    public function testAHeldCheckIsStoredWithItsReason(): void
    {
        $id = $this->received(self::PARTNER_ON, 'a.edi');
        $check = new RemittanceCheck('CHK1', '20261020', 60.0, 'TEST PAYER', [$this->claim('41-141', '2', 70.0)], 0.0, []);

        (new AutoPostRepository())->record($id, 1, CheckRecord::held($check, HoldReason::OutOfBalance), new \DateTimeImmutable('2026-11-19 12:00:00'));

        $this->assertSame(
            ['status' => 'held', 'hold_reason' => 'out_of_balance', 'payer_id' => '0', 'session_id' => '0'],
            $this->strings(QueryUtils::querySingleRow(
                'SELECT `status`, `hold_reason`, `payer_id`, `session_id` FROM `era_auto_post_check` WHERE `received_id` = ?',
                [$id]
            ) ?: [])
        );
        $this->assertSame(
            ['outcome' => 'review', 'reasons' => 'check_held', 'pid' => '0', 'payer_level' => '2'],
            $this->strings(QueryUtils::querySingleRow(
                'SELECT c.`outcome`, c.`reasons`, c.`pid`, c.`payer_level` FROM `era_auto_post_claim` AS c'
                . ' JOIN `era_auto_post_check` AS k ON k.`id` = c.`check_id` WHERE k.`received_id` = ?',
                [$id]
            ) ?: [])
        );
    }

    public function testThePartnersPostingUserIsReadOfferedAndCleared(): void
    {
        $partners = new ReceivePartnerRepository();
        $users = [];
        foreach ($partners->partnersWithFolders() as $partner) {
            $users[$partner->id] = $partner->autoPostUser;
        }
        $this->assertSame(self::USER, $users[self::PARTNER_ON] ?? null);
        $this->assertSame(0, $users[self::PARTNER_OFF] ?? null);

        // Active users are offered; an inactive one only to the partner that has it.
        $choices = $partners->autoPostUserChoices(null);
        $this->assertSame('Poster, Auto (autopost' . self::USER . ')', $choices[self::USER] ?? null);
        $this->assertArrayNotHasKey(self::INACTIVE_USER, $choices);
        $this->assertArrayHasKey(self::INACTIVE_USER, $partners->autoPostUserChoices((string) self::INACTIVE_USER));

        // Choosing "off" on the form submits it empty.
        $emptied = ReceivePartnerRepository::emptiedFolderColumns(['x12_era_auto_post_user' => '', 'x12_sftp_receive_dir_835' => '/remits']);
        $this->assertSame(['x12_era_auto_post_user'], $emptied);
        $partners->clearFolders(self::PARTNER_ON, $emptied);
        $this->assertNull(QueryUtils::fetchSingleValue(
            'SELECT `x12_era_auto_post_user` FROM `x12_partners` WHERE `id` = ?',
            'x12_era_auto_post_user',
            [self::PARTNER_ON]
        ));
    }

    private function received(
        int $partnerId,
        string $name,
        string $type = 'f835',
        string $status = 'stored',
        string $autoPostStatus = 'pending',
    ): int {
        return QueryUtils::sqlInsert(
            'INSERT INTO `x12_remote_received` (`remote_key`, `x12_partner_id`, `remote_dir`, `remote_name`, `remote_size`,'
            . ' `file_type`, `status`, `stored_name`, `received_at`, `auto_post_status`) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, ?)',
            [hash('sha256', $partnerId . $name), $partnerId, '/remits', $name, $type, $status, $name, self::RECEIVED, $autoPostStatus]
        );
    }

    private function claim(string $ourClaimId, string $status, float $paid): ClaimPayment
    {
        return new ClaimPayment('CHK1', '20261020', '20261019', $ourClaimId, $status, 'ICN' . $ourClaimId, '20260915', 'ANNE', 'POSTING', '', 0, [], $paid);
    }

    private function autoPostStatus(int $id): string
    {
        $status = QueryUtils::fetchSingleValue('SELECT `auto_post_status` FROM `x12_remote_received` WHERE `id` = ?', 'auto_post_status', [$id]);
        return is_string($status) ? $status : '';
    }

    /**
     * @param list<PendingRemittance> $pending
     * @return list<PendingRemittance> those of this test's partners
     */
    private function ours(array $pending): array
    {
        $ids = QueryUtils::fetchTableColumn(
            'SELECT `id` FROM `x12_remote_received` WHERE `x12_partner_id` IN (?, ?, ?)',
            'id',
            [self::PARTNER_ON, self::PARTNER_OFF, self::PARTNER_INACTIVE_USER]
        );
        $ids = array_map(static fn(mixed $id): int => is_numeric($id) ? (int) $id : 0, $ids);
        return array_values(array_filter($pending, static fn(PendingRemittance $file): bool => in_array($file->receivedId, $ids, true)));
    }

    /**
     * @param array<mixed> $row
     * @return array<string, string>
     */
    private function strings(array $row): array
    {
        $strings = [];
        foreach ($row as $column => $value) {
            $strings[(string) $column] = is_scalar($value) ? (string) $value : '';
        }
        return $strings;
    }

    private function cleanUp(): void
    {
        $partners = [self::PARTNER_ON, self::PARTNER_OFF, self::PARTNER_INACTIVE_USER];
        QueryUtils::sqlStatementThrowException(
            'DELETE c FROM `era_auto_post_claim` AS c JOIN `era_auto_post_check` AS k ON k.`id` = c.`check_id`'
            . ' JOIN `x12_remote_received` AS r ON r.`id` = k.`received_id` WHERE r.`x12_partner_id` IN (?, ?, ?)',
            $partners
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE k FROM `era_auto_post_check` AS k JOIN `x12_remote_received` AS r ON r.`id` = k.`received_id`'
            . ' WHERE r.`x12_partner_id` IN (?, ?, ?)',
            $partners
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `x12_remote_received` WHERE `x12_partner_id` IN (?, ?, ?)', $partners);
        QueryUtils::sqlStatementThrowException('DELETE FROM `x12_partners` WHERE `id` IN (?, ?, ?)', $partners);
        QueryUtils::sqlStatementThrowException('DELETE FROM `users` WHERE `id` IN (?, ?)', [self::USER, self::INACTIVE_USER]);
    }
}
