<?php

/**
 * Identifier for the three sibling release PRs orchestrated by ship-release:
 *
 *   Conductor — release-prep/<rel-branch> on openemr/openemr, merges into
 *               the rel branch and triggers annotated release tag creation
 *               via release-prep.yml's finalize job.
 *   Docs      — release-docs/<version> on openemr/website-openemr, merges
 *               into master, brings acknowledgements + release-status +
 *               EHI/b10 SchemaSpy content.
 *   Finalize  — release-finalize/<rel-branch> on openemr/openemr, merges
 *               into master, brings release-targets.yml pin update +
 *               prep-for-next-cycle mutations.
 *
 * Merge order is Conductor → Docs → Finalize (locked in
 * PullRequestTarget::forRelease and enforced in
 * ShipReleaseOrchestrator::sortByMergeOrder). Finalize is last because
 * (a) its content depends on the tag existing so the post-tag mutator
 * output reflects reality, and (b) merging release-targets.yml on master
 * triggers downstream docker builds + demo_farm reconciliation, which
 * should fire only once the release is truly done.
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
