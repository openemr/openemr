<?php

/**
 * Builds an IncomeSummary from an income determination and FPL guideline.
 *
 * Pure presentation logic (no database): runs the FPL calculation and the
 * sliding-fee mapping, then formats the result for display. Unit-testable in
 * isolation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Income;

use OpenEMR\FQHC\Fpl\FederalPovertyGuideline;
use OpenEMR\FQHC\Fpl\FplCalculator;
use OpenEMR\FQHC\Fpl\IncomeDetermination;
use OpenEMR\FQHC\Fpl\SlidingFeeSchedule;

final class IncomeSummaryFactory
{
    private FplCalculator $calculator;
    private SlidingFeeSchedule $schedule;

    public function __construct(?FplCalculator $calculator = null, ?SlidingFeeSchedule $schedule = null)
    {
        $this->calculator = $calculator ?? new FplCalculator();
        $this->schedule = $schedule ?? new SlidingFeeSchedule();
    }

    public function create(IncomeDetermination $income, FederalPovertyGuideline $guideline): IncomeSummary
    {
        $fpl = $this->calculator->calculate($income, $guideline);
        $tier = $this->schedule->tierFor($fpl->band);

        return new IncomeSummary(
            recorded: $income->isDeterminable(),
            householdSize: $income->householdSize,
            annualIncomeDisplay: $income->annualIncome !== null ? $this->money($income->annualIncome) : null,
            fplPercent: $fpl->percent,
            bandLabel: $fpl->band->label(),
            tierLabel: $tier->label(),
        );
    }

    private function money(float $amount): string
    {
        return '$' . number_format($amount, 0);
    }
}
