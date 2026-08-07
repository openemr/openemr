<?php

/**
 * Deterministic Clock for orchestrator tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release\Fakes;

use OpenEMR\Release\Clock;

final class FakeClock implements Clock
{
    private \DateTimeImmutable $current;

    public int $totalSlept = 0;

    public function __construct(?\DateTimeImmutable $start = null)
    {
        $this->current = $start ?? new \DateTimeImmutable('2026-01-01T00:00:00Z');
    }

    public function now(): \DateTimeImmutable
    {
        return $this->current;
    }

    public function sleep(int $seconds): void
    {
        $this->totalSlept += $seconds;
        $this->current = $this->current->modify("+{$seconds} seconds");
    }
}
