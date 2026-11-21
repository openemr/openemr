<?php

/**
 * A contact_relation from a patient to a planned person.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

use OpenEMR\DemoSeed\SeedFile\RelationFlags;

final readonly class PlannedRelation
{
    public function __construct(
        public int $ownerPid,
        public string $personRef,
        public string $relationship,
        public string $role,
        public RelationFlags $flags,
        public int $priority,
    ) {
    }
}
