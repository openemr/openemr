<?php

/**
 * Database tests for the x12_remote_received log and the partner query.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing\X12Receive;

use OpenEMR\Billing\X12Receive\ReceivedFileRepository;
use OpenEMR\Billing\X12Receive\ReceivedStatus;
use OpenEMR\Billing\X12Receive\ReceivePartnerRepository;
use OpenEMR\Billing\X12Receive\RemoteFile;
use OpenEMR\Billing\X12Receive\StoreOutcome;
use OpenEMR\Billing\X12Receive\X12FileType;
use OpenEMR\Billing\X12Receive\X12ReceiveService;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class ReceivedFileRepositoryTest extends TestCase
{
    private const PARTNER_ID = 987650;
    private const FOLDER = '/x12-receive-test';

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `x12_remote_received` WHERE `x12_partner_id` = ?', [self::PARTNER_ID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `x12_partners` WHERE `id` IN (?, ?)', [self::PARTNER_ID, self::PARTNER_ID + 1]);
    }

    public function testAFileIsSettledOnceStoredAndTriedAgainAfterAFailure(): void
    {
        $log = new ReceivedFileRepository();
        $file = new RemoteFile('status.277', 648, 1767268800);
        $at = new \DateTimeImmutable('2026-11-10 12:00:00');

        $this->assertFalse($log->isSettled(self::PARTNER_ID, self::FOLDER, $file));

        $log->record(self::PARTNER_ID, self::FOLDER, $file, X12FileType::ClaimStatus277, new StoreOutcome(ReceivedStatus::Failed, message: 'The download did not complete'), $at);
        $this->assertFalse($log->isSettled(self::PARTNER_ID, self::FOLDER, $file));

        $log->record(self::PARTNER_ID, self::FOLDER, $file, X12FileType::ClaimStatus277, new StoreOutcome(ReceivedStatus::Stored, 'status.277'), $at->modify('+1 hour'));
        $this->assertTrue($log->isSettled(self::PARTNER_ID, self::FOLDER, $file));

        $rows = QueryUtils::fetchRecords(
            'SELECT `remote_name`, `remote_size`, `file_type`, `status`, `stored_name`, `message`, `received_at` FROM `x12_remote_received` WHERE `x12_partner_id` = ?',
            [self::PARTNER_ID]
        );
        $this->assertCount(1, $rows, 'the second record updates the first; one row per remote file');
        $this->assertEquals(
            ['remote_name' => 'status.277', 'remote_size' => 648, 'file_type' => 'f277', 'status' => 'stored', 'stored_name' => 'status.277', 'message' => '', 'received_at' => '2026-11-10 13:00:00'],
            $rows[0]
        );
    }

    public function testAChangedFileOfTheSameNameIsANewFile(): void
    {
        $log = new ReceivedFileRepository();
        $log->record(self::PARTNER_ID, self::FOLDER, new RemoteFile('status.277', 648, 1767268800), X12FileType::ClaimStatus277, new StoreOutcome(ReceivedStatus::Stored, 'status.277'), new \DateTimeImmutable('2026-11-10 12:00:00'));

        $this->assertFalse($log->isSettled(self::PARTNER_ID, self::FOLDER, new RemoteFile('status.277', 700, 1767268800)));
        $this->assertFalse($log->isSettled(self::PARTNER_ID, self::FOLDER, new RemoteFile('status.277', 648, 1767355200)));
        $this->assertFalse($log->isSettled(self::PARTNER_ID + 1, self::FOLDER, new RemoteFile('status.277', 648, 1767268800)));
    }

    public function testAFileOfNoKnownKindIsRecordedWithoutAType(): void
    {
        $log = new ReceivedFileRepository();
        $file = new RemoteFile('readme.txt', 15, 1767268800);

        $log->record(self::PARTNER_ID, self::FOLDER, $file, null, new StoreOutcome(ReceivedStatus::Rejected, message: 'Not an X12 835, 277 file'), new \DateTimeImmutable('2026-11-10 12:00:00'));

        $this->assertTrue($log->isSettled(self::PARTNER_ID, self::FOLDER, $file));
        $this->assertSame('', QueryUtils::fetchSingleValue('SELECT `file_type` FROM `x12_remote_received` WHERE `x12_partner_id` = ?', 'file_type', [self::PARTNER_ID]));
    }

    public function testOnlyPartnersWithAHostAndAReceiveFolderAreReturned(): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `x12_partners` (`id`, `name`, `x12_sftp_host`, `x12_sftp_port`, `x12_sftp_login`, `x12_sftp_receive_dir_277`) VALUES (?, 'receive test', 'sftp.example.test', '22', 'biller', '/277')",
            [self::PARTNER_ID]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `x12_partners` (`id`, `name`, `x12_sftp_host`) VALUES (?, 'send only test', 'sftp.example.test')",
            [self::PARTNER_ID + 1]
        );

        $ours = array_values(array_filter(
            (new ReceivePartnerRepository())->partnersWithFolders(),
            static fn($partner): bool => in_array($partner->id, [self::PARTNER_ID, self::PARTNER_ID + 1], true)
        ));

        $this->assertCount(1, $ours);
        $this->assertSame(self::PARTNER_ID, $ours[0]->id);
        $this->assertSame(['f277' => '/277'], $ours[0]->folders);
    }

    public function testClearFoldersNullsOnlyTheNamedReceiveFolders(): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `x12_partners` (`id`, `name`, `x12_sftp_host`, `x12_sftp_remote_dir`, `x12_sftp_receive_dir_835`, `x12_sftp_receive_dir_277`) VALUES (?, 'receive test', 'sftp.example.test', '/claims', '/remits', '/277')",
            [self::PARTNER_ID]
        );

        (new ReceivePartnerRepository())->clearFolders(self::PARTNER_ID, ['x12_sftp_receive_dir_277', 'x12_sftp_remote_dir', 'name']);

        $row = QueryUtils::querySingleRow(
            'SELECT `name`, `x12_sftp_remote_dir`, `x12_sftp_receive_dir_835`, `x12_sftp_receive_dir_277` FROM `x12_partners` WHERE `id` = ?',
            [self::PARTNER_ID]
        );
        $this->assertSame(
            ['name' => 'receive test', 'x12_sftp_remote_dir' => '/claims', 'x12_sftp_receive_dir_835' => '/remits', 'x12_sftp_receive_dir_277' => null],
            $row
        );
    }

    public function testTheBackgroundServiceRowNamesACallableMethod(): void
    {
        $function = QueryUtils::fetchSingleValue("SELECT `function` FROM `background_services` WHERE `name` = 'X12_SFTP_Receive'", 'function');

        $this->assertIsCallable($function);
        $this->assertSame(X12ReceiveService::class . '::runBackgroundService', $function);
    }
}
