<?php

/**
 * One questionnaire's row in the availability grid.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\Super\QuestionnaireAvailability;

final readonly class GridRow
{
    /**
     * @param string $filterText lower-cased name and category, matched by the filter box
     * @param int $extraRules rules beyond a plain tick: limits and permission overrides
     * @param list<GridCell> $cells one per surface, in surface order
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $categoryLabel,
        public string $filterText,
        public bool $selected,
        public int $extraRules,
        public array $cells,
    ) {
    }
}
