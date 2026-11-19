<?php

/**
 * AutoPostLog kept in memory, for the unattended posting tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EraAutoPost;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Billing\EraAutoPost\AutoPostLog;
use OpenEMR\Billing\EraAutoPost\CheckRecord;
use OpenEMR\Billing\EraAutoPost\PendingRemittance;

final class InMemoryAutoPostLog implements AutoPostLog
{
    /** @var array<int, true> */
    public array $taken = [];
    /** @var array<int, AutoPostFileStatus> */
    public array $finished = [];
    /** @var list<array{int, int, CheckRecord, \DateTimeImmutable}> */
    public array $records = [];

    /**
     * @param list<PendingRemittance> $pending
     */
    public function __construct(private readonly array $pending)
    {
    }

    public function pending(): array
    {
        return $this->pending;
    }

    public function take(PendingRemittance $file): bool
    {
        if (isset($this->taken[$file->receivedId])) {
            return false;
        }
        $this->taken[$file->receivedId] = true;
        return true;
    }

    public function finish(int $receivedId, AutoPostFileStatus $status): void
    {
        $this->finished[$receivedId] = $status;
    }

    public function record(int $receivedId, int $sequence, CheckRecord $check, \DateTimeImmutable $at): void
    {
        $this->records[] = [$receivedId, $sequence, $check, $at];
    }
}
