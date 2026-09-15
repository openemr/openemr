<?php

/**
 * Whether a PR is ready to merge, with one human-readable reason per blocker.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class PullRequestReadiness
{
    /**
     * @param list<string> $blockingReasons
     */
    public function __construct(
        public string $headRefOid,
        public array $blockingReasons,
    ) {
    }

    public function isReady(): bool
    {
        return $this->blockingReasons === [];
    }
}
