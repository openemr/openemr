<?php

/**
 * VisitListMove
 *
 * One Visit List patient to move to a block occurrence, after the block was saved with a new
 * date, start time or provider.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

final readonly class VisitListMove
{
    /**
     * @param bool $unlessClaimed only move the patient when no Visit List block claims it where
     *                            it is now: a patient a shortened block no longer reaches may
     *                            already be on another list
     */
    public function __construct(
        public VisitListEntry $entry,
        public VisitListBlock $to,
        public bool $unlessClaimed = false,
    ) {
    }
}
