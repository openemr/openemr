<?php

/**
 * Per-PR outcome record produced by ShipReleaseOrchestrator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class ShipReleaseStepResult
{
    /**
     * @param list<string> $reasons
     */
    public function __construct(
        public PullRequestTarget $target,
        public ShipReleaseStepStatus $status,
        public ?int $prNumber,
        public ?string $mergeSha,
        public array $reasons,
    ) {
    }
}
