<?php

/**
 * Outcome of refreshing the docs PR snapshot + readiness right before merge.
 *
 * Replaces an earlier 4-tuple with overloaded null semantics. Only one of the
 * three static factories produces a usable instance; the type system enforces
 * which fields are populated.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class DownstreamRefreshResult
{
    /**
     * @param list<string> $blockingReasons
     */
    private function __construct(
        public ?PullRequestSnapshot $snapshot,
        public ?PullRequestReadiness $readiness,
        public ?string $stopReason,
        public array $blockingReasons,
    ) {
    }

    public static function success(PullRequestSnapshot $snapshot, PullRequestReadiness $readiness): self
    {
        return new self($snapshot, $readiness, null, []);
    }

    /**
     * @param list<string> $reasons
     */
    public static function blocked(?PullRequestSnapshot $snapshot, string $stopReason, array $reasons): self
    {
        return new self($snapshot, null, $stopReason, $reasons);
    }

    public function isSuccess(): bool
    {
        return $this->stopReason === null;
    }
}
