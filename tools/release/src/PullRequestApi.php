<?php

/**
 * Cross-repo PR operations the ship-release orchestrator needs. Kept narrow on
 * purpose so tests can substitute a fake without standing up the full gh CLI.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

interface PullRequestApi
{
    /**
     * Look up the most recent PR (open or closed/merged) whose head branch
     * matches. Returns null when no PR exists for that branch.
     */
    public function findByHead(string $repo, string $branch): ?PullRequestSnapshot;

    /**
     * Compute readiness from GitHub's mergeability + checks + review state.
     *
     * When $requireApproval is false, the reviewDecision !== APPROVED check is
     * skipped -- the not-draft + MERGEABLE + CLEAN + status-check gates still
     * apply. Docs + Finalize PRs are auto-generated bot content with no
     * meaningful "human review" gate; Conductor is the one meaningful review
     * point and defaults to requiring approval for back-compat.
     */
    public function getReadiness(string $repo, int $number, bool $requireApproval = true): PullRequestReadiness;

    /**
     * Whether a GitHub Release object exists for $tag in $repo. Used by the
     * full-auto orchestrator to gate downstream PR merges on
     * build-release-on-tag having completed package assembly + upload.
     */
    public function releaseExists(string $repo, string $tag): bool;

    /**
     * POST a commit status to a SHA. Used to publish release/ship-approved
     * before merge so branch protection can require it.
     */
    public function postCommitStatus(
        string $repo,
        string $sha,
        string $context,
        string $state,
        string $description,
        string $targetUrl,
    ): void;

    /**
     * Squash-merge a PR, refusing to proceed if the PR's current head SHA has
     * moved since $expectedHeadSha. Returns the merge commit SHA.
     */
    public function squashMerge(string $repo, int $number, string $expectedHeadSha): string;
}
