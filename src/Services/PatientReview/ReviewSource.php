<?php

/**
 * Where a review request came from.
 *
 * Stored in patient_review_request.source, so the values are a persistence contract.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

enum ReviewSource: string
{
    /** The patient portal, or staff acting in the portal dashboard. */
    case Portal = 'portal';
    /** A patient-facing API app; the request also records the OAuth2 client id. */
    case Api = 'api';
}
