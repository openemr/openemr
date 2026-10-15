<?php

/**
 * ValidationResult - result of a schematron validation pass.
 *
 * Shape mirrors what the legacy Node service (oe-schematron-service) returned
 * over HTTP: three parallel lists (errors, warnings, ignored) plus counts.
 * `toArray()` reproduces the exact JSON shape callers historically consumed
 * so downstream renderers require no change.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda\Schematron;

final readonly class ValidationResult
{
    /**
     * @param list<array<string, mixed>> $errors
     * @param list<array<string, mixed>> $warnings
     * @param list<array<string, mixed>> $ignored
     */
    public function __construct(
        public array $errors,
        public array $warnings,
        public array $ignored,
    ) {
    }

    /**
     * @return array{
     *   errorCount: int, warningCount: int, ignoredCount: int,
     *   errors: list<array<string, mixed>>,
     *   warnings: list<array<string, mixed>>,
     *   ignored: list<array<string, mixed>>,
     * }
     */
    public function toArray(): array
    {
        return [
            'errorCount' => count($this->errors),
            'warningCount' => count($this->warnings),
            'ignoredCount' => count($this->ignored),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'ignored' => $this->ignored,
        ];
    }
}
