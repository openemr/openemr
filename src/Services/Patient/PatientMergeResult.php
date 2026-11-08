<?php

/**
 * PatientMergeResult is what {@see PatientMergeService::merge()} hands back to its caller.
 *
 * A merge either runs to completion or aborts partway through; either way the steps completed
 * before that point are reported, because the legacy page showed them and an operator needs to
 * know how far a failed merge got before it stopped.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2026 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Patient;

final readonly class PatientMergeResult
{
    /**
     * @param list<string> $steps        Human-readable, unescaped description of what the merge did.
     * @param ?string      $errorMessage Translated, user-safe reason the merge stopped, or null.
     */
    private function __construct(
        public bool $successful,
        public array $steps,
        public ?string $errorMessage,
    ) {
    }

    /**
     * @param list<string> $steps
     */
    public static function completed(array $steps): self
    {
        return new self(true, $steps, null);
    }

    /**
     * @param list<string> $steps
     */
    public static function failed(array $steps, string $errorMessage): self
    {
        return new self(false, $steps, $errorMessage);
    }
}
