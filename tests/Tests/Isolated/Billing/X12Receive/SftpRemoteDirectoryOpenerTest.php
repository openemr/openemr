<?php

/**
 * Isolated tests for SftpRemoteDirectoryOpener's handling of a password that cannot be decrypted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\X12Receive;

use OpenEMR\Billing\X12Receive\ReceivePartner;
use OpenEMR\Billing\X12Receive\SftpRemoteDirectoryOpener;
use OpenEMR\Common\Crypto\CryptoGenException;
use OpenEMR\Common\Crypto\CryptoInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

class SftpRemoteDirectoryOpenerTest extends TestCase
{
    public function testPasswordThatCannotBeDecryptedIsLoggedAndThePartnerSkipped(): void
    {
        $crypto = $this->createMock(CryptoInterface::class);
        $crypto->expects($this->once())
            ->method('decryptFromDatabase')
            ->with('unreadable')
            ->willThrowException(new CryptoGenException('Decryption failed'));
        $logger = new class extends AbstractLogger {
            /** @var list<array{string, string, array<array-key, mixed>}> */
            public array $records = [];

            /**
             * @param array<array-key, mixed> $context
             */
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [is_string($level) ? $level : '', (string) $message, $context];
            }
        };
        // No connection is attempted: the host is not resolvable and the test would hang or fail if it were.
        $partner = new ReceivePartner(646, 'sftp.invalid', 22, 'biller', 'unreadable', ['f277' => '/277'], '');

        $remote = (new SftpRemoteDirectoryOpener($crypto, $logger))->open($partner);

        $this->assertNull($remote);
        $this->assertCount(1, $logger->records);
        $this->assertSame('error', $logger->records[0][0]);
        $this->assertSame(646, $logger->records[0][2]['partner'] ?? null);
        $this->assertInstanceOf(CryptoGenException::class, $logger->records[0][2]['exception'] ?? null);
    }
}
