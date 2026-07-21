<?php

/**
 * Aggregated outcome of a ship-release run.
 *
 * Successful when every step is MERGED, SKIPPED_ALREADY_MERGED, or WOULD_MERGE
 * (dry-run) AND no fatal reason was recorded. A BLOCKED, NOT_REACHED, or
 * fatalReason makes the run a failure.
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
        $successful = [
            ShipReleaseStepStatus::MERGED,
            ShipReleaseStepStatus::SKIPPED_ALREADY_MERGED,
            ShipReleaseStepStatus::WOULD_MERGE,
        ];
        foreach ($this->steps as $step) {
            if (!in_array($step->status, $successful, true)) {
                return false;
            }
        }
        return true;
    }
}
