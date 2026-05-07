<?php

/**
 * Inputs to TagCreator::create(). Carries the version-derived tag
 * name, the merge commit SHA, the conductor PR URL, the date the
 * release was cut, and the App-token credentials required for the
 * GitHub API calls.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class TagCreationRequest
{
    public function __construct(
        public string $repo,
        public string $version,
        public string $commitSha,
        public string $conductorPrUrl,
        public string $appToken,
        public string $date,
        public string $taggerName = 'openemr-release-bot',
        public string $taggerEmail = 'release-bot@openemr.invalid',
    ) {
        if (preg_match('/^\d+\.\d+\.\d+$/', $version) !== 1) {
            throw new \InvalidArgumentException('version must be MAJOR.MINOR.PATCH; got: ' . $version);
        }
        if (preg_match('/^[0-9a-f]{40}$/', $commitSha) !== 1) {
            throw new \InvalidArgumentException('commitSha must be 40 hex characters; got: ' . $commitSha);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            throw new \InvalidArgumentException('date must be ISO YYYY-MM-DD; got: ' . $date);
        }
        if ($appToken === '') {
            throw new \InvalidArgumentException('appToken is required');
        }
        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*\/[A-Za-z0-9._-]+$/', $repo) !== 1) {
            throw new \InvalidArgumentException('repo must be owner/name; got: ' . $repo);
        }
    }

    public function tagName(): string
    {
        return 'v' . str_replace('.', '_', $this->version);
    }

    public function renderMessage(): string
    {
        $template = <<<'TPL'
        OpenEMR %s released %s

        Conductor PR: %s
        Merge commit: %s

        Created by openemr-release-bot via automation
        TPL;
        return sprintf($template, $this->version, $this->date, $this->conductorPrUrl, $this->commitSha);
    }
}
