<?php

/**
 * VisitListMovePlan
 *
 * What moving a saved block's patients comes to: the patients to move, each to its block
 * occurrence, the patients whose occurrence has no counterpart after the save, who stay
 * where they are, no longer on a list, and the patients on dates before today, who are not
 * moved.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

final readonly class VisitListMovePlan
{
    /**
     * @param list<VisitListMove>  $moves    patients whose date, start or provider changes
     * @param list<VisitListEntry> $stranded patients left without a block occurrence
     * @param list<VisitListEntry> $past     patients on dates before today, left where they are
     */
    public function __construct(
        public array $moves,
        public array $stranded,
        public array $past = [],
    ) {
    }
}
