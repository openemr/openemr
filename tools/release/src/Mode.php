<?php

/**
 * Operator-selected ship-release execution mode.
 *
 *   DryRun    — Preflight only for this CLI: probe every PR's readiness and
 *               print a report; merge nothing. The workflow (ship-release.yml)
 *               layers a dry-run-build job on top of this mode that, when
 *               preflight succeeds, calls build-release.yml with dry_run=true
 *               pinned to the Conductor PR head (release-prep/<rel_branch>)
 *               so the packaged tree carries the pending version bump +
 *               CHANGELOG entry. Produces the actual tarball + zip +
 *               changelog + checksums as run artifacts (no git tag, no
 *               GitHub Release, no downstream dispatches). Same as the
 *               pre-3b `--dry-run` flag; kept reachable via that flag for
 *               muscle memory + script compat.
 *
 *   SemiAuto  — Preflight + merge Conductor PR only. Docs and Finalize PRs
 *               stay untouched -- maintainer merges them manually after
 *               eyeballing the post-tag content. Intended for the first 1-2
 *               releases after wiring up the automation, so any surprising
 *               mutator/EHI/finalize output can be caught and fixed before
 *               committing to full-auto. Default until we've built confidence.
 *
 *   FullAuto  — Preflight + merge all three PRs. Conductor first, then
 *               waits for the GitHub Release object to exist (proxy for
 *               build-release-on-tag completing package assembly +
 *               upload; blocks both downstream merges if packaging
 *               failed so dockers + announcements don't fire on top of
 *               a broken release), then merges Finalize (which triggers
 *               the docker cascade via its release-targets.yml update
 *               on master), then Docs last. Docs is last because merging
 *               it will trigger the future auto-announce pipeline; by
 *               then packages exist (verified) and dockers are building.
 *               Real "one command go." Requires the docs-PR auto-flip
 *               (Phase 3c) on openemr/website-openemr to be truly
 *               hands-off; without it, the maintainer still has to
 *               manually flip the docs PR to ready-for-review before
 *               ship-release can merge it.
 *
 * When both `--dry-run` and `--mode=<X>` are provided, dry-run wins (safest
 * behavior for a redundant/conflicting configuration).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

enum Mode: string
{
    case DryRun = 'dry-run';
    case SemiAuto = 'semi-auto';
    case FullAuto = 'full-auto';

    /**
     * Whether this mode should perform any merges. DryRun probes only.
     */
    public function performsMerges(): bool
    {
        return match ($this) {
            self::DryRun => false,
            self::SemiAuto, self::FullAuto => true,
        };
    }

    /**
     * Whether this mode should merge the Docs + Finalize PRs (in addition to
     * Conductor). SemiAuto stops after Conductor; FullAuto proceeds through
     * all three.
     */
    public function mergesDownstream(): bool
    {
        return match ($this) {
            self::DryRun, self::SemiAuto => false,
            self::FullAuto => true,
        };
    }
}
