<?php

/**
 * PatientMergeRequest describes a single chart merge for {@see PatientMergeService::merge()}.
 *
 * Target and source are both patient ids, so they are trivially transposable as bare arguments.
 * Naming them on a DTO makes the direction of the merge explicit at every call site.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

final readonly class PatientMergeRequest
{
    /**
     * @param int  $targetPid          The chart that is kept and receives the merged data.
     * @param int  $sourcePid          The chart that is merged into the target and then deleted.
     * @param bool $skipIdentityChecks When true the SSN/DOB equality checks are skipped. The
     *                                 duplicate manager sets this because it has already decided
     *                                 the two charts are the same person.
     */
    public function __construct(
        public int $targetPid,
        public int $sourcePid,
        public bool $skipIdentityChecks = false,
    ) {
    }
}
