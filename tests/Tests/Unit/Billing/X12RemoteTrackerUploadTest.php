<?php

/**
 * Tests the status recorded for an X12 claim file upload over SFTP.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Unit\Billing;

use OpenEMR\Billing\BillingProcessor\X12RemoteTracker;
use phpseclib3\Net\SFTP;
use PHPUnit\Framework\TestCase;

class X12RemoteTrackerUploadTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function waitingRow(): array
    {
        return [
            'id' => 7,
            'x12_filename' => 'batch-20261001.txt',
            'status' => X12RemoteTracker::STATUS_IN_PROGRESS,
            'messages' => null,
        ];
    }

    public function testSuccessfulUploadIsMarkedSuccess(): void
    {
        $sftp = $this->createMock(SFTP::class);
        $sftp->expects($this->once())
            ->method('put')
            ->with('batch-20261001.txt', 'ISA*00*~')
            ->willReturn(true);
        $sftp->expects($this->never())->method('getSFTPErrors');

        $row = X12RemoteTracker::uploadClaimFile($sftp, $this->waitingRow(), 'ISA*00*~');

        $this->assertSame(X12RemoteTracker::STATUS_SUCCESS, $row['status']);
        $this->assertNull($row['messages']);
    }

    public function testFailedUploadIsMarkedUploadErrorNotSuccess(): void
    {
        $sftp = $this->createMock(SFTP::class);
        $sftp->method('put')->willReturn(false);
        $sftp->method('getSFTPErrors')->willReturn(['NET_SFTP_STATUS_PERMISSION_DENIED: Permission denied']);

        $row = X12RemoteTracker::uploadClaimFile($sftp, $this->waitingRow(), 'ISA*00*~');

        $this->assertSame(X12RemoteTracker::STATUS_UPLOAD_ERRROR, $row['status']);
        $this->assertSame(
            ['Could not upload file.', 'NET_SFTP_STATUS_PERMISSION_DENIED: Permission denied'],
            $row['messages']
        );
    }

    public function testFailedUploadKeepsEarlierMessages(): void
    {
        $sftp = $this->createMock(SFTP::class);
        $sftp->method('put')->willReturn(false);
        $sftp->method('getSFTPErrors')->willReturn([]);

        $row = $this->waitingRow();
        $row['messages'] = ['Earlier message.'];
        $row = X12RemoteTracker::uploadClaimFile($sftp, $row, 'ISA*00*~');

        $this->assertSame(X12RemoteTracker::STATUS_UPLOAD_ERRROR, $row['status']);
        $this->assertSame(['Earlier message.', 'Could not upload file.'], $row['messages']);
    }
}
