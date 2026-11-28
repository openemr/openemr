<?php

/**
 * VisitListMoveResult
 *
 * How moving a saved block's patients went: how many moved, how many have no block occurrence
 * to go to, how many multi-provider copies stayed because the new provider already had a copy
 * of that appointment on that date, and how many on dates before today were left alone.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

final readonly class VisitListMoveResult
{
    public function __construct(
        public int $moved,
        public int $stranded,
        public int $keptForOtherCopy,
        public int $leftOnPastDates = 0,
        public int $takenByOtherBlock = 0,
    ) {
    }

    public static function nothing(): self
    {
        return new self(0, 0, 0);
    }
}
