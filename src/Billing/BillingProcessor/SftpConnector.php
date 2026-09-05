<?php

/**
 * Opens SFTP connections, retrying transient connection failures.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\BillingProcessor;

use phpseclib3\Exception\ConnectionClosedException;
use phpseclib3\Exception\UnableToConnectException;
use phpseclib3\Net\SFTP;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * phpseclib connects lazily inside login(), so a connection that cannot be
 * established throws rather than returning false. Those are the failures worth
 * retrying. A false return means the handshake completed and the server
 * rejected the credentials, which will not improve on a retry, and replaying
 * bad credentials risks tripping a clearinghouse lockout, so that case is
 * reported without retrying.
 */
class SftpConnector
{
    /**
     * Number of times to attempt a connection before giving up.
     */
    public const DEFAULT_ATTEMPTS = 4;

    /**
     * Seconds to wait after each failed attempt, indexed by completed attempt.
     * Needs at least DEFAULT_ATTEMPTS - 1 entries.
     */
    public const DEFAULT_BACKOFF_SECONDS = [1, 3, 8];

    /**
     * How many connection attempts failed during the last connect() call.
     */
    private int $failedAttempts = 0;

    private bool $credentialsRejected = false;

    private readonly int $attempts;

    private readonly LoggerInterface $logger;

    /**
     * @param int                  $attempts       Connection attempts before giving up.
     * @param int[]                $backoffSeconds Wait before each retry.
     * @param LoggerInterface|null $logger         Receives the raw transport errors.
     */
    public function __construct(
        int $attempts = self::DEFAULT_ATTEMPTS,
        private array $backoffSeconds = self::DEFAULT_BACKOFF_SECONDS,
        ?LoggerInterface $logger = null
    ) {
        $this->attempts = max(1, $attempts);
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return SFTP|null Null when no connection could be established. A
     *                   connection is still returned when the credentials were
     *                   rejected, so its own errors remain readable.
     */
    public function connect(string $host, int $port, string $login, ?string $password): ?SFTP
    {
        $this->failedAttempts = 0;
        $this->credentialsRejected = false;

        for ($attempt = 1; $attempt <= $this->attempts; $attempt++) {
            try {
                $sftp = new SFTP($host, $port);
                $this->credentialsRejected = $sftp->login($login, $password) === false;
                return $sftp;
            } catch (UnableToConnectException | ConnectionClosedException $e) {
                // The raw transport error can carry host and protocol detail, and
                // the caller persists its messages where any user with billing
                // write access can read them, so the detail goes to the log and
                // only the attempt count is handed back.
                $this->logger->warning('SFTP connection attempt failed', [
                    'attempt' => $attempt,
                    'attempts' => $this->attempts,
                    'error' => $e->getMessage(),
                ]);
                $this->failedAttempts++;
                if ($attempt < $this->attempts) {
                    sleep($this->backoffSeconds[$attempt - 1] ?? 1);
                }
            }
        }

        return null;
    }

    public function getFailedAttempts(): int
    {
        return $this->failedAttempts;
    }

    public function credentialsRejected(): bool
    {
        return $this->credentialsRejected;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }
}
