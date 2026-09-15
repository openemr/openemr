<?php

/**
 * FakePullRequestApi variant that throws on squashMerge for one specific PR.
 * Used to verify the orchestrator catches gh failures per-step.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release\Fakes;

final class FailingMergeApi extends FakePullRequestApi
{
    public function __construct(
        private readonly string $failRepo,
        private readonly int $failNumber,
        private readonly string $failMessage,
    ) {
    }

    public function squashMerge(string $repo, int $number, string $expectedHeadSha): string
    {
        if ($repo === $this->failRepo && $number === $this->failNumber) {
            throw new \RuntimeException($this->failMessage);
        }
        return parent::squashMerge($repo, $number, $expectedHeadSha);
    }
}
