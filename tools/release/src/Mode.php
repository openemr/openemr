<?php

/**
 * Operator-selected ship-release execution mode.
 *
 *   DryRun    — Preflight only: probe every PR's readiness and print a report;
 *               merge nothing. Same as the pre-3b `--dry-run` flag; kept
 *               reachable via that flag for muscle memory + script compat.
 *
 *   SemiAuto  — Preflight + merge Conductor PR only. Docs and Finalize PRs
 *               stay untouched -- maintainer merges them manually after
 *               eyeballing the post-tag content. Intended for the first 1-2
 *               releases after wiring up the automation, so any surprising
 *               mutator/EHI/finalize output can be caught and fixed before
 *               committing to full-auto. Default until we've built confidence.
 *
 *   FullAuto  — Preflight + merge all three PRs. After merging Conductor,
 *               wait for the GitHub Release object to exist (proxy for
 *               build-release-on-tag completing package assembly + upload),
 *               then merge Docs, then Finalize. Real "one command go."
 *               Requires the docs-PR auto-flip (Phase 3c) on
 *               openemr/website-openemr to be truly hands-off; without it,
 *               the maintainer still has to manually flip the docs PR to
 *               ready-for-review before ship-release can merge it.
 *
 * When both `--dry-run` and `--mode=<X>` are provided, dry-run wins (safest
 * behavior for a redundant/conflicting configuration).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
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
