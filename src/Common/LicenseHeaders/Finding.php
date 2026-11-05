<?php

/**
 * One header problem in one file
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

final readonly class Finding
{
    /**
     * @param string $path relative to the project root, with forward slashes
     */
    public function __construct(
        public string $path,
        public int $line,
        public Rule $rule,
        public string $message,
    ) {
    }
}
