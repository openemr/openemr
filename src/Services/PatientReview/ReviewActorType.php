<?php

/**
 * Who caused a state change on a review request.
 *
 * Stored in patient_review_event.actor_type, so the values are a persistence contract.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

enum ReviewActorType: string
{
    case Patient = 'patient';
    case User = 'user';
    case System = 'system';
}
