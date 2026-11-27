<?php

/**
 * What the ERA worklist page reads and marks.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

interface Worklist
{
    /**
     * The claim payments unattended posting left for review, newest check first. The claim
     * payments of a check that was held are not among them: the check is worked as a
     * whole, from its file.
     *
     * @return WorklistPage<ReviewClaim>
     */
    public function reviewClaims(WorklistFilter $filter, int $limit): WorklistPage;

    /**
     * The checks unattended posting posted, newest first, each with its claim payments.
     *
     * @return WorklistPage<PostedCheck>
     */
    public function postedChecks(WorklistFilter $filter, int $limit): WorklistPage;

    /**
     * The stored 835s that unattended posting did not finish, or that hold a check it
     * held, newest first.
     *
     * @return WorklistPage<ReceivedFile>
     */
    public function receivedFiles(WorklistFilter $filter, int $limit): WorklistPage;

    /**
     * The payers that have a check on the worklist, as their 835s name them.
     *
     * @return list<string>
     */
    public function payerNames(): array;

    public function openCounts(): OpenCounts;

    /**
     * @return bool false when the claim payment is not one left for review, or is already marked
     */
    public function markClaimWorked(int $claimId, WorklistUser $by, \DateTimeImmutable $at): bool;

    /**
     * @return bool false when the claim payment was not marked
     */
    public function clearClaimWorked(int $claimId): bool;

    /**
     * @return bool false when the file is not on the worklist, or is already marked
     */
    public function markFileWorked(int $receivedId, WorklistUser $by, \DateTimeImmutable $at): bool;

    /**
     * @return bool false when the file was not marked
     */
    public function clearFileWorked(int $receivedId): bool;
}
