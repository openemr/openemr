<?php

/**
 * Aggregated outcome of a ship-release run.
 *
 * Successful when every step is MERGED, SKIPPED_ALREADY_MERGED, SKIPPED_BY_MODE
 * (semi-auto intentionally leaving downstream PRs for manual merge), or
 * WOULD_MERGE (dry-run) AND no fatal reason was recorded. A BLOCKED,
 * NOT_REACHED, or fatalReason makes the run a failure.
 *
 * NOT_REACHED means "downstream step didn't run because an upstream step
 * failed" — that's a real failure. SKIPPED_BY_MODE means "we intentionally
 * didn't run this step because the mode says so" — the operator got exactly
 * what they asked for, so it exits 0.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class ShipReleaseResult
{
    /**
     * @param list<ShipReleaseStepResult> $steps
     */
    public function __construct(
        public array $steps,
        public ?string $fatalReason = null,
    ) {
    }

    public function wasSuccessful(): bool
    {
        if ($this->fatalReason !== null) {
            return false;
        }
        // Exhaustive match without default so PHPStan flags new enum
        // cases at compile time (per CLAUDE.md coding guideline).
        foreach ($this->steps as $step) {
            $isSuccess = match ($step->status) {
                ShipReleaseStepStatus::MERGED,
                ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED,
                ShipReleaseStepStatus::SKIPPED_BY_MODE,
                ShipReleaseStepStatus::WOULD_MERGE => true,
                ShipReleaseStepStatus::BLOCKED,
                ShipReleaseStepStatus::NOT_REACHED => false,
            };
            if (!$isSuccess) {
                return false;
            }
        }
        return true;
    }
}
