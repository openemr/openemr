<?php

/**
 * DTO for the ClaimRev module-version-check API response.
 *
 * The check is best-effort: callers should treat null as "no information"
 * and proceed normally. A populated result indicates ClaimRev's view of
 * this install's version status — see the field comments for what each
 * field means.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector\Dto;

final readonly class ModuleVersionCheckResult
{
    public function __construct(
        /** Latest published version of the module per ClaimRev. */
        public string $currentVersion,
        /** True if the install is on the latest published version. */
        public bool $isCurrent,
        /** False if the version is past end-of-support and should be upgraded. */
        public bool $isSupported,
        /** Human-readable note for the operator (may be empty). */
        public string $message,
        /** info | warning | critical — drives the dashboard banner color. */
        public string $severity,
        /** Direct download URL for the latest version (may be empty). */
        public string $downloadUrl,
        /** When true, ClaimRev has flagged this version as must-not-run. */
        public bool $disabled,
        /** Human-readable explanation when disabled is true (may be empty). */
        public string $disableReason,
    ) {
    }

    /**
     * Build from a decoded API response. Returns null if the payload
     * is malformed (missing required fields, wrong types, etc.) so the
     * caller can treat malformed and unreachable identically.
     *
     * @param array<string, mixed> $raw
     */
    public static function fromApi(array $raw): ?self
    {
        $currentVersion = $raw['currentVersion'] ?? null;
        if (!is_string($currentVersion) || $currentVersion === '') {
            return null;
        }
        $severity = $raw['severity'] ?? 'info';
        if (!in_array($severity, ['info', 'warning', 'critical'], true)) {
            $severity = 'info';
        }

        return new self(
            currentVersion: $currentVersion,
            isCurrent: (bool) ($raw['isCurrent'] ?? false),
            isSupported: (bool) ($raw['isSupported'] ?? true),
            message: is_string($raw['message'] ?? null) ? $raw['message'] : '',
            severity: $severity,
            downloadUrl: is_string($raw['downloadUrl'] ?? null) ? $raw['downloadUrl'] : '',
            disabled: (bool) ($raw['disabled'] ?? false),
            disableReason: is_string($raw['disableReason'] ?? null) ? $raw['disableReason'] : '',
        );
    }
}
