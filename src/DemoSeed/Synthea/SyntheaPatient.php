<?php

/**
 * One patient from a Synthea run: its files and the facts the loader needs
 * that the CCDA import doesn't keep (marital status, payer).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Synthea;

final readonly class SyntheaPatient
{
    /**
     * @param string $syntheaId Synthea's patient UUID, the same in the CCDA
     *        (patientRole/id/@extension) and the FHIR file name.
     * @param string $sex FHIR administrative gender (male, female, ...).
     * @param ?string $maritalStatus FHIR v3 MaritalStatus code (M, S, D, W, ...).
     * @param ?string $payer Payer on the patient's most recent Synthea claim;
     *        null when Synthea gave none or "NO_INSURANCE".
     */
    public function __construct(
        public string $syntheaId,
        public string $ccdaFile,
        public string $fhirFile,
        public string $firstName,
        public string $lastName,
        public \DateTimeImmutable $birthDate,
        public string $sex,
        public ?string $maritalStatus,
        public ?string $payer,
    ) {
    }

    public function ageOn(\DateTimeImmutable $date): int
    {
        return $this->birthDate->diff($date)->y;
    }

    /**
     * @return array{synthea_id: string, ccda_file: string, fhir_file: string, first_name: string, last_name: string, birth_date: string, sex: string, marital_status: ?string, payer: ?string}
     */
    public function toArray(): array
    {
        return [
            'synthea_id' => $this->syntheaId,
            'ccda_file' => $this->ccdaFile,
            'fhir_file' => $this->fhirFile,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'birth_date' => $this->birthDate->format('Y-m-d'),
            'sex' => $this->sex,
            'marital_status' => $this->maritalStatus,
            'payer' => $this->payer,
        ];
    }
}
