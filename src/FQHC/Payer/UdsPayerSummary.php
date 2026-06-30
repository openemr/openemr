<?php

/**
 * Display-ready UDS payer summary for the Snapshot insurance card.
 *
 * `classified` is false when coverage exists but its type could not be mapped
 * to a UDS bucket (the card then prompts to configure the mapping). With no
 * coverage, the category is None/uninsured and `classified` is true.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Payer;

final readonly class UdsPayerSummary
{
    public function __construct(
        public bool $hasCoverage,
        public bool $classified,
        public string $categoryLabel,
        public ?string $planName,
    ) {
    }
}
