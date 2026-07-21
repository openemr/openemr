<?php

/**
 * One PR slot in the ship-release orchestration: which repo, which head
 * branch, what role it plays, and the order in which it merges.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class PullRequestTarget
{
    public function __construct(
        public string $repo,
        public string $branch,
        public string $expectedBase,
        public RoleLabel $roleLabel,
        public int $mergeOrder,
    ) {
    }

    /**
     * Build the canonical conductor → docs → finalize target list for a release.
     *
     * Branch name conventions are defined in openemr-devops#705 and #664.
     * Conductor merges into the rel-<n> branch, docs into master, finalize
     * into master.
     *
     * @return list<self>
     */
    public static function forRelease(string $version, string $relBranch): array
    {
        return [
            new self('openemr/openemr', "release-prep/{$relBranch}", $relBranch, RoleLabel::Conductor, 1),
            new self('openemr/website-openemr', "release-docs/{$version}", 'master', RoleLabel::Docs, 2),
            new self('openemr/openemr', "release-finalize/{$relBranch}", 'master', RoleLabel::Finalize, 3),
        ];
    }
}
