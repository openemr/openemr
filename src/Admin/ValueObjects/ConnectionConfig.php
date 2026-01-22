<?php

/**
 * Connection Configuration Value Object
 *
 * Encapsulates connection pool configuration settings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\ValueObjects;

class ConnectionConfig
{
    private readonly int $maxRetries;
    private readonly int $retryDelayMicros;

    public function __construct(int $maxRetries = 3, int $retryDelayMicros = 100000)
    {
        if ($maxRetries < 1) {
            throw new \InvalidArgumentException('maxRetries must be at least 1');
        }

        if ($retryDelayMicros < 0) {
            throw new \InvalidArgumentException('retryDelayMicros must be non-negative');
        }

        $this->maxRetries = $maxRetries;
        $this->retryDelayMicros = $retryDelayMicros;
    }

    public function getMaxRetries(): int
    {
        return $this->maxRetries;
    }

    public function getRetryDelayMicros(): int
    {
        return $this->retryDelayMicros;
    }
}
