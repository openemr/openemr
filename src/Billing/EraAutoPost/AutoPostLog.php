<?php

/**
 * The record of unattended posting runs: which received 835s are waiting, and what was done with each.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

interface AutoPostLog
{
    /**
     * Stored 835s no run has taken, received at or after $since from a partner that has
     * an active user to post as.
     *
     * @return list<PendingRemittance>
     */
    public function pending(\DateTimeImmutable $since): array;

    /**
     * Mark the file as taken by this run. False when another run took it first.
     */
    public function take(int $receivedId): bool;

    public function finish(int $receivedId, AutoPostFileStatus $status): void;

    /**
     * @param int $sequence the check's position in the file, from 1
     */
    public function record(int $receivedId, int $sequence, CheckRecord $check, \DateTimeImmutable $at): void;
}
