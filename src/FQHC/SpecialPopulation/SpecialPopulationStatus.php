<?php

/**
 * A recorded special-population status for a patient.
 *
 * Pairs a population with an optional, validated subtype and an "as of" date.
 * The subtype is checked against the population's allowed set at construction
 * (and a subtype on a population that has none is rejected), so downstream code
 * and reports never see an invalid pairing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\SpecialPopulation;

use DomainException;

final readonly class SpecialPopulationStatus
{
    public function __construct(
        public SpecialPopulation $population,
        public ?string $subtype = null,
        public ?string $asOfDate = null,
    ) {
        if ($subtype !== null && !array_key_exists($subtype, $population->subtypeOptions())) {
            throw new DomainException('Invalid subtype "' . $subtype . '" for population ' . $population->value);
        }
    }

    public function subtypeLabel(): ?string
    {
        if ($this->subtype === null) {
            return null;
        }

        return $this->population->subtypeOptions()[$this->subtype] ?? null;
    }

    public function displayLabel(): string
    {
        $subtypeLabel = $this->subtypeLabel();

        return $subtypeLabel === null
            ? $this->population->label()
            : $this->population->label() . ' — ' . $subtypeLabel;
    }
}
