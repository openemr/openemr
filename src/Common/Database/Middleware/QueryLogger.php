<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Database\Middleware;

use Firehed\DbalLogger\DbalLogger;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Writes queries to a PSR-3 logger
 */
final class QueryLogger implements DbalLogger
{
    private ?string $sql = null;
    private ?float $start = null;

    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function startQuery(string $sql, ?array $params = null, ?array $types = null): void
    {
        $this->sql = $sql;
        $this->start = hrtime(true);
    }

    public function stopQuery(?Throwable $exception): void
    {
        if ($this->sql === null) {
            return;
        }

        $durationNs = hrtime(true) - $this->start;
        $durationMs = $durationNs / 1_000_000;

        $this->logger->debug('SQL Query complete ({result}) in {sec}ms: {sql}', [
            'result' => $exception === null ? 'success' : 'error',
            'sec' => sprintf('%0.1f', $durationMs),
            'sql' => $this->sql,
        ]);
        $this->sql = null;
        $this->start = null;
    }

    public function connect(): void
    {
        // No-op for now
    }

    public function disconnect(): void
    {
        // No-op for now
    }
}
