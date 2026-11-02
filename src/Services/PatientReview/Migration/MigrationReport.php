<?php

/**
 * What one run of the onsite_portal_activity migration did.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Migration;

final class MigrationReport
{
    public int $migrated = 0;

    /** @var list<int> legacy ids left in onsite_portal_activity */
    public array $skipped = [];

    /** @var list<int> legacy ids whose write failed; retried on the next upgrade pass */
    public array $failed = [];

    /** @var array<int, string> legacy status keyed by legacy id, for rows migrated as pending */
    public array $unrecognised = [];
}
