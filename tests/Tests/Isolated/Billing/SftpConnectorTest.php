<?php

/**
 * Tests the SFTP connection retry policy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\BillingProcessor\SftpConnector;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;

class SftpConnectorTest extends TestCase
{
    /**
     * Nothing listens here, so the lazy connect inside phpseclib's login()
     * raises UnableToConnectException immediately. Loopback keeps it fast and
     * deterministic, with no outbound network.
     */
    private const UNUSED_PORT = 47999;

    public function testConnectFailureIsRetriedAndReportedRatherThanThrown(): void
    {
        $connector = new SftpConnector(3, [0, 0]);

        $sftp = $connector->connect('127.0.0.1', self::UNUSED_PORT, 'someuser', 'somepassword');

        $this->assertNull($sftp, 'No connection should be returned when the host is unreachable.');
        $this->assertSame(
            3,
            $connector->getFailedAttempts(),
            'Every attempt should be counted as a failure.'
        );
        $this->assertFalse(
            $connector->credentialsRejected(),
            'A connection failure is not a credentials rejection.'
        );
    }

    public function testSingleAttemptDoesNotRetry(): void
    {
        $connector = new SftpConnector(1, []);

        $sftp = $connector->connect('127.0.0.1', self::UNUSED_PORT, 'someuser', 'somepassword');

        $this->assertNull($sftp);
        $this->assertSame(1, $connector->getFailedAttempts());
    }

    /**
     * An attempt count below one would skip the loop entirely and report no
     * error at all, so it is clamped.
     */
    public function testAttemptCountIsClampedToAtLeastOne(): void
    {
        $connector = new SftpConnector(0, []);

        $this->assertSame(1, $connector->getAttempts());
        $this->assertNull($connector->connect('127.0.0.1', self::UNUSED_PORT, 'someuser', 'somepassword'));
        $this->assertSame(1, $connector->getFailedAttempts());
    }

    /**
     * The failure count from a previous call must not accumulate.
     */
    public function testFailedAttemptCountIsResetBetweenCalls(): void
    {
        $connector = new SftpConnector(2, [0]);

        $connector->connect('127.0.0.1', self::UNUSED_PORT, 'someuser', 'somepassword');
        $this->assertSame(2, $connector->getFailedAttempts());

        $connector->connect('127.0.0.1', self::UNUSED_PORT, 'someuser', 'somepassword');
        $this->assertSame(2, $connector->getFailedAttempts());
    }

    /**
     * The raw transport error is what the tracker must not persist, so it has
     * to reach the log instead, and only through the context array.
     */
    public function testRawTransportErrorGoesToTheLoggerNotTheCaller(): void
    {
        $logger = new class extends AbstractLogger {
            /** @var list<array{string, string, array<mixed>}> */
            public array $records = [];

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = [
                    is_string($level) ? $level : get_debug_type($level),
                    (string) $message,
                    $context,
                ];
            }
        };

        $connector = new SftpConnector(2, [0], $logger);
        $connector->connect('127.0.0.1', self::UNUSED_PORT, 'someuser', 'somepassword');

        $this->assertCount(2, $logger->records, 'Each failed attempt should be logged.');
        $this->assertSame('warning', $logger->records[0][0]);
        $this->assertStringNotContainsString(
            'Connection refused',
            $logger->records[0][1],
            'The log message itself should stay fixed.'
        );
        $error = $logger->records[0][2]['error'] ?? null;
        $this->assertIsString($error, 'The raw error detail should be a string.');
        $this->assertStringContainsString(
            'Connection refused',
            $error,
            'The raw detail belongs in the context array.'
        );
        $this->assertSame(1, $logger->records[0][2]['attempt']);
        $this->assertSame(2, $logger->records[1][2]['attempt']);
    }

    /**
     * Omitting the logger must not blow up.
     */
    public function testConnectWorksWithoutALogger(): void
    {
        $connector = new SftpConnector(2, [0]);

        $this->assertNull($connector->connect('127.0.0.1', self::UNUSED_PORT, 'someuser', 'somepassword'));
        $this->assertSame(2, $connector->getFailedAttempts());
    }
}
