<?php

/**
 * Where the claim statuses read from a 277 are kept.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

/**
 * The index of 277 claim statuses (#15530 step 2). An interface so the indexer is
 * tested without a database, as the receive service's log is.
 */
interface ClaimStatusIndexStore
{
    /**
     * Whether a file of this content was already indexed, under any name.
     */
    public function isIndexed(string $fileSha256): bool;

    /**
     * Keep every claim of one file, all or nothing.
     *
     * @param list<array{ack: array<string, mixed>, statuses: list<array<string, mixed>>, references: list<array{qualifier: string, ref_value: string}>}> $claims
     */
    public function recordFile(array $claims): void;
}