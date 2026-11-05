<?php

/**
 * The outcome of checking one file
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\LicenseHeaders;

final readonly class FileResult
{
    /**
     * @param list<Finding> $findings empty for exempt and compliant files
     */
    private function __construct(
        public string $path,
        public int $lines,
        public bool $exempt,
        public array $findings,
    ) {
    }

    public static function exempt(string $path, int $lines): self
    {
        return new self($path, $lines, true, []);
    }

    /**
     * @param list<Finding> $findings
     */
    public static function checked(string $path, int $lines, array $findings): self
    {
        return new self($path, $lines, false, $findings);
    }

    public function isCompliant(): bool
    {
        return !$this->exempt && $this->findings === [];
    }
}
