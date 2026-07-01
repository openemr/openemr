<?php

/**
 * One countable visit for UDS Table 5 utilization.
 *
 * Parsed reporting input: the visit's service category has been resolved and it
 * has already been established that this is a countable visit (placeholder
 * calendar categories are excluded at the data boundary, not here). `virtual`
 * distinguishes Column B2 (virtual visits) from Column B (in-person visits); the
 * patient id feeds the within-category unduplicated patient count (Column C).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

final readonly class Table5VisitRecord
{
    public function __construct(
        public int $pid,
        public UdsServiceCategory $category,
        public bool $virtual,
    ) {
    }
}
