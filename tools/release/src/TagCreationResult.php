<?php

/**
 * Outcome of TagCreator::create(): the tag name created and the SHA
 * of the resulting tag object (not the commit object).
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class TagCreationResult
{
    public function __construct(
        public string $tagName,
        public string $tagSha,
    ) {
    }
}
