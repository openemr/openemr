<?php

/**
 * Point-in-time snapshot of a pull request.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class PullRequestSnapshot
{
    public function __construct(
        public int $number,
        public string $headRefOid,
        public string $baseRefName,
        public PullRequestState $state,
    ) {
    }

    public function isMerged(): bool
    {
        return $this->state === PullRequestState::Merged;
    }

    public function isClosed(): bool
    {
        return $this->state === PullRequestState::Closed;
    }
}
