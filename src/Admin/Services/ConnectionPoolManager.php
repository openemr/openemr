<?php

/**
 * Connection Pool Manager Service
 *
 * Manages database connection pooling with retry logic and exponential backoff.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Services;

use OpenEMR\Admin\Contracts\DatabaseConnectorInterface;
use OpenEMR\Admin\Exceptions\DatabaseConnectionException;
use OpenEMR\Admin\ValueObjects\ConnectionConfig;
use OpenEMR\Admin\ValueObjects\DatabaseCredentials;

class ConnectionPoolManager implements DatabaseConnectorInterface
{
    /**
     * Connection pool to prevent database connection exhaustion
     * @var array<string, \mysqli>
     */
    private array $connectionPool = [];

    public function __construct(private readonly ConnectionConfig $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getConnection(DatabaseCredentials $credentials): \mysqli
    {
        $poolKey = $credentials->getPoolKey();

        // Check if connection already exists in pool and is valid
        if (isset($this->connectionPool[$poolKey]) && $this->connectionPool[$poolKey] instanceof \mysqli) {
            if (@mysqli_ping($this->connectionPool[$poolKey])) {
                return $this->connectionPool[$poolKey];
            }
            // Connection is stale, remove it
            unset($this->connectionPool[$poolKey]);
        }

        // Attempt connection with retry logic
        $connection = $this->attemptConnection($credentials);

        // Store in pool
        $this->connectionPool[$poolKey] = $connection;

        return $connection;
    }

    /**
     * Attempt database connection with retry logic
     */
    private function attemptConnection(DatabaseCredentials $credentials): \mysqli
    {
        $attempt = 0;
        $maxRetries = $this->config->getMaxRetries();
        $retryDelay = $this->config->getRetryDelayMicros();
        $lastError = '';

        while ($attempt < $maxRetries) {
            try {
                $dbh = @mysqli_connect(
                    $credentials->getHost(),
                    $credentials->getLogin(),
                    $credentials->getPass(),
                    $credentials->getDbase(),
                    $credentials->getPort()
                );

                if ($dbh) {
                    // Set connection timeouts to prevent hanging connections
                    mysqli_options($dbh, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
                    mysqli_options($dbh, MYSQLI_OPT_READ_TIMEOUT, 10);

                    return $dbh;
                }

                $lastError = mysqli_connect_error() ?? 'Unknown error';

                // If "too many connections", wait before retry
                if (stripos($lastError, 'too many connections') !== false) {
                    error_log("MySQL connection pool exhausted (attempt " . ($attempt + 1) . "/{$maxRetries})");
                    $attempt++;
                    if ($attempt < $maxRetries) {
                        usleep($retryDelay * $attempt); // Exponential backoff
                    }
                    continue;
                }

                // For other errors, fail immediately - log only generic message
                error_log("Database connection failed for site configuration");
                throw new DatabaseConnectionException(
                    "Database connection failed",
                    $credentials->toSanitizedArray(),
                    $attempt + 1
                );
            } catch (\Exception $e) {
                error_log("Database connection exception occurred");
                throw new DatabaseConnectionException(
                    "Database connection error: " . $e->getMessage(),
                    $credentials->toSanitizedArray(),
                    $attempt + 1,
                    0,
                    $e
                );
            }
        }

        // All retries exhausted
        error_log("Database connection failed after {$maxRetries} attempts");
        throw new DatabaseConnectionException(
            "Database connection failed after {$maxRetries} attempts",
            $credentials->toSanitizedArray(),
            $maxRetries
        );
    }

    /**
     * {@inheritdoc}
     */
    public function closeAllConnections(): void
    {
        foreach ($this->connectionPool as $key => $conn) {
            if ($conn instanceof \mysqli) {
                @mysqli_close($conn);
            }
            // Explicitly unset to clear memory
            unset($this->connectionPool[$key]);
        }
        // Clear the entire pool array
        $this->connectionPool = [];
    }

    /**
     * Destructor to ensure connections are closed
     */
    public function __destruct()
    {
        $this->closeAllConnections();
    }
}
