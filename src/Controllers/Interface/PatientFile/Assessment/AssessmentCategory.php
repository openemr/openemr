<?php

/**
 * One category's group of cards on the Available Assessments tab.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

final readonly class AssessmentCategory
{
    /**
     * @param string $section the user setting that remembers it folded, also the group's element id
     * @param list<AssessmentCard> $cards
     */
    public function __construct(
        public string $section,
        public string $title,
        public bool $collapsed,
        public array $cards,
    ) {
    }
}
