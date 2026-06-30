<?php

/**
 * A single UDS field shown on the Patient Snapshot.
 *
 * Either carries a recorded value or is empty (rendered as an empty-state). The
 * `isRecorded()` distinction is what drives "shown as data" vs. "not yet
 * recorded" in the UI.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Snapshot;

final readonly class UdsField
{
    public function __construct(
        public string $label,
        public ?string $value,
    ) {
    }

    public function isRecorded(): bool
    {
        return $this->value !== null && trim($this->value) !== '';
    }
}
