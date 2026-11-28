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

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;

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
     * Take a file from unattended posting so that a person can post it: one waiting for a
     * posting run is no longer for unattended posting, and one a run took and did not
     * finish is recorded as interrupted. Only a person can say that the run has stopped.
     *
     * @param AutoPostFileStatus $seen the state the person saw the file in
     * @return bool false when the file is no longer in that state, or the state is not one a file is taken from
     */
    public function takeFromUnattended(int $receivedId, AutoPostFileStatus $seen): bool;

    /**
     * @param ?AutoPostFileStatus $seen     the unattended posting state the person saw the file in
     * @param int                 $heldSeen how many held checks of the file the person saw
     * @return bool false when the file is not on the worklist, is waiting for or held by a posting run, is
     *              already marked, or is no longer as the person saw it
     */
    public function markFileWorked(int $receivedId, WorklistUser $by, \DateTimeImmutable $at, ?AutoPostFileStatus $seen, int $heldSeen): bool;

    /**
     * @return bool false when the file was not marked
     */
    public function clearFileWorked(int $receivedId): bool;
}
