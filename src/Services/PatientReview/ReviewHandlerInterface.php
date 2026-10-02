<?php

/**
 * Applies an approved review request to the chart.
 *
 * One handler per ReviewType. The handler writes through the normal service for that data;
 * ReviewQueueService owns the state change around it. A handler for patient-app FHIR writes
 * plugs in here later.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

interface ReviewHandlerInterface
{
    public function type(): ReviewType;

    /**
     * Runs inside the transaction that approves the request. Throwing rolls the approval back
     * and leaves the request pending.
     */
    public function apply(ReviewRequest $request, ReviewActor $reviewer): void;
}
