<?php

/**
 * How patient_review_request.payload is encoded.
 *
 * New requests are always Json. The other two exist only so rows migrated from
 * onsite_portal_activity.table_args stay readable: profile changes were PHP-serialized and
 * everything else was stored as raw text.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

enum PayloadFormat: string
{
    case Json = 'json';
    case PhpSerialized = 'php-serialized';
    case Text = 'text';
}
