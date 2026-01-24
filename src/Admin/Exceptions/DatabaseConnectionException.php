<?php

/**
 * Database Connection Exception
 *
 * Thrown when database connection fails after retries.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Exceptions;

class DatabaseConnectionException extends SiteAdminException
{
    /**
     * @param array<string, mixed> $sanitizedCredentials
     */
    public function __construct(
        string $message,
        private readonly array $sanitizedCredentials = [],
        private readonly int $attemptCount = 0,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSanitizedCredentials(): array
    {
        return $this->sanitizedCredentials;
    }

    public function getAttemptCount(): int
    {
        return $this->attemptCount;
    }
}
