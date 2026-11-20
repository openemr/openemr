<?php

/**
 * Which patients a rule applies to (`when`) or a case picks (`pick`).
 * Every given part must match; an empty condition matches everyone.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\SeedFile;

use OpenEMR\DemoSeed\Synthea\SyntheaPatient;

final readonly class PatientCondition
{
    /**
     * @param list<string> $syntheaMarital FHIR marital codes from Synthea (M, S, D, W, ...)
     */
    public function __construct(
        public ?AgeRange $age = null,
        public ?Gender $sex = null,
        public array $syntheaMarital = [],
    ) {
    }

    public static function any(): self
    {
        return new self();
    }

    public static function parse(?MapReader $map): self
    {
        if ($map === null) {
            return self::any();
        }
        $map->rejectUnknownKeys(['age', 'sex', 'synthea_marital']);
        $age = $map->optionalString('age');
        $sex = $map->optionalString('sex');
        $gender = null;
        if ($sex !== null) {
            $gender = match (strtolower($sex)) {
                'male', 'm' => Gender::Male,
                'female', 'f' => Gender::Female,
                default => throw SeedFileException::at($map->pathOf('sex'), 'must be Male or Female'),
            };
        }
        return new self(
            $age === null ? null : AgeRange::fromString($age, $map->pathOf('age')),
            $gender,
            $map->stringList('synthea_marital'),
        );
    }

    public function matches(SyntheaPatient $patient, \DateTimeImmutable $referenceDate): bool
    {
        if ($this->age !== null && !$this->age->contains($patient->ageOn($referenceDate))) {
            return false;
        }
        if ($this->sex !== null && $patient->gender() !== $this->sex) {
            return false;
        }
        if ($this->syntheaMarital !== [] && !in_array($patient->maritalStatus, $this->syntheaMarital, true)) {
            return false;
        }
        return true;
    }
}
