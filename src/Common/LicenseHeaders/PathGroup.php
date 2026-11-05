<?php

/**
 * A set of path patterns from the policy file, with the reason they are grouped
 *
 * Patterns use fnmatch() without FNM_PATHNAME, so `*` also matches `/`:
 * `src/FHIR/R4/*` covers every file below that directory.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

final readonly class PathGroup
{
    /**
     * @param non-empty-list<non-empty-string> $patterns
     */
    public function __construct(
        public string $reason,
        public array $patterns,
    ) {
    }

    public function matches(string $path): bool
    {
        foreach ($this->patterns as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }
        return false;
    }
}
