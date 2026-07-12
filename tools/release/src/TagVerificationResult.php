<?php

/**
 * Result of verifying a release tag against the openemr-devops#664 spec.
 *
 * @package   openemr-devops
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr-devops/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

final readonly class TagVerificationResult
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public string $tagName,
        public bool $isAnnotated,
        public ?string $version,
        public ?string $date,
        public ?string $mergeSha,
        public array $errors,
    ) {
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
