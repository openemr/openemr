<?php

/**
 * The results for every file in one scan
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

final readonly class ScanResult
{
    /**
     * @param list<FileResult> $files
     */
    public function __construct(
        public array $files,
    ) {
    }

    /**
     * @return list<Finding>
     */
    public function findings(): array
    {
        $findings = [];
        foreach ($this->files as $file) {
            array_push($findings, ...$file->findings);
        }
        return $findings;
    }

    public function hasFindings(): bool
    {
        foreach ($this->files as $file) {
            if ($file->findings !== []) {
                return true;
            }
        }
        return false;
    }
}
