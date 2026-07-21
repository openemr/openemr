<?php

/**
 * Identifier for the three sibling release PRs orchestrated by ship-release:
 *
 *   Conductor — release-prep/<rel-branch> on openemr/openemr, merges into
 *               the rel branch and triggers annotated release tag creation
 *               via release-prep.yml's finalize job.
 *   Finalize  — release-finalize/<rel-branch> on openemr/openemr, merges
 *               into master, brings release-targets.yml pin update +
 *               prep-for-next-cycle mutations. The release-targets.yml
 *               update triggers downstream docker cascade + demo_farm
 *               reconciliation.
 *   Docs      — release-docs/<version> on openemr/website-openemr, merges
 *               into master, brings acknowledgements + release-status +
 *               EHI/b10 SchemaSpy content. Will trigger release-
 *               announcements.yml (future) on the docs-PR-merged signal.
 *
 * Merge order is Conductor → Finalize → Docs (locked in
 * PullRequestTarget::forRelease and enforced in
 * ShipReleaseOrchestrator::sortByMergeOrder).
 *
 * Docs is last because merging it triggers auto-announce (future); by that
 * point packages should exist (Full-auto waits for the GitHub Release
 * object before merging Docs) and dockers should be building.
 *
 * Finalize is before Docs so its release-targets.yml update starts the
 * docker cascade earlier -- dockers take longer to build than packages,
 * so give them a head start. Dockers also publish on cron independently,
 * so they'll catch up even if the announce beats them; this ordering is
 * nice-to-have, not strictly required.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

enum RoleLabel: string
{
    case Conductor = 'conductor';
    case Docs = 'docs';
    case Finalize = 'finalize';
}
