<?php

/**
 * Aggregated day sheet totals: per-user, per-provider, and grand totals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\DaySheet;

final readonly class DaySheetTotals
{
    /**
     * @param list<SlotTotals> $userTotals  Slots with at least one non-zero field.
     * @param list<SlotTotals> $providerTotals  Slots with at least one non-zero field.
     */
    public function __construct(
        public array $userTotals,
        public array $providerTotals,
        public SlotTotals $grandTotals,
    ) {
    }
}
