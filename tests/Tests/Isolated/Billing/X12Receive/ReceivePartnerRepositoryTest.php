<?php

/**
 * Isolated tests for building a ReceivePartner from an x12_partners row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\X12Receive;

use OpenEMR\Billing\X12Receive\ReceivePartnerRepository;
use PHPUnit\Framework\TestCase;

class ReceivePartnerRepositoryTest extends TestCase
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function row(array $overrides = []): array
    {
        return $overrides + [
            'id' => '646',
            'x12_sftp_host' => ' sftp.example.test ',
            'x12_sftp_port' => '2222',
            'x12_sftp_login' => 'biller',
            'x12_sftp_pass' => 'encrypted',
            'x12_sftp_receive_dir_835' => '/remits/',
            'x12_sftp_receive_dir_277' => '/277',
            'x12_sftp_receive_dir_999' => null,
            'x12_sftp_receive_worked_dir' => '/WorkedTrans/',
        ];
    }

    public function testRowBecomesPartnerWithOnlyTheFoldersThatAreSet(): void
    {
        $partner = ReceivePartnerRepository::fromRow($this->row());

        $this->assertNotNull($partner);
        $this->assertSame(646, $partner->id);
        $this->assertSame('sftp.example.test', $partner->host);
        $this->assertSame(2222, $partner->port);
        $this->assertSame('biller', $partner->login);
        $this->assertSame('encrypted', $partner->encryptedPassword);
        $this->assertSame(['f835' => '/remits', 'f277' => '/277'], $partner->folders);
        $this->assertSame('/WorkedTrans', $partner->workedFolder);
    }

    public function testTheRemoteRootIsAFolder(): void
    {
        $partner = ReceivePartnerRepository::fromRow($this->row([
            'x12_sftp_receive_dir_835' => '/',
            'x12_sftp_receive_dir_277' => '',
            'x12_sftp_receive_worked_dir' => '/',
        ]));

        $this->assertNotNull($partner);
        $this->assertSame(['f835' => '/'], $partner->folders);
        $this->assertSame('/', $partner->workedFolder);
    }

    public function testPortDefaultsTo22AndPasswordMayBeMissing(): void
    {
        $partner = ReceivePartnerRepository::fromRow($this->row(['x12_sftp_port' => '', 'x12_sftp_pass' => null]));

        $this->assertNotNull($partner);
        $this->assertSame(22, $partner->port);
        $this->assertNull($partner->encryptedPassword);
    }

    public function testOnlyReceiveFoldersPostedEmptyAreReportedAsEmptied(): void
    {
        $posted = [
            'x12_sftp_receive_dir_835' => '/remits',
            'x12_sftp_receive_dir_277' => '  ',
            'x12_sftp_receive_worked_dir' => '',
            'x12_sftp_remote_dir' => '',
            'name' => '',
        ];

        $this->assertSame(
            ['x12_sftp_receive_dir_277', 'x12_sftp_receive_worked_dir'],
            ReceivePartnerRepository::emptiedFolderColumns($posted)
        );
        $this->assertSame([], ReceivePartnerRepository::emptiedFolderColumns([]));
    }

    public function testNoFoldersNoHostOrNoIdIsNotAReceivePartner(): void
    {
        $noFolders = ['x12_sftp_receive_dir_835' => '', 'x12_sftp_receive_dir_277' => ' ', 'x12_sftp_receive_dir_999' => null];
        $this->assertNull(ReceivePartnerRepository::fromRow($this->row($noFolders)));
        $this->assertNull(ReceivePartnerRepository::fromRow($this->row(['x12_sftp_host' => ''])));
        $this->assertNull(ReceivePartnerRepository::fromRow($this->row(['id' => null])));
    }
}
