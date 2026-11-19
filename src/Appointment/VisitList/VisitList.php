<?php

/**
 * VisitList
 *
 * One Visit List block on one date and the patients on it, in list order.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Appointment\VisitList;

final readonly class VisitList
{
    /**
     * @param list<VisitListEntry> $entries in list order
     */
    public function __construct(
        public VisitListBlock $block,
        public array $entries,
    ) {
    }
}
