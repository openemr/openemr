<?php

/**
 * One patient from a Synthea run: its files and the facts the loader needs
 * that the CCDA import doesn't keep (marital status, payer, the state as a
 * list code).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Synthea;

use OpenEMR\DemoSeed\PostalAddress;
use OpenEMR\DemoSeed\SeedFile\Gender;

final readonly class SyntheaPatient
{
    /**
     * @param string $syntheaId Synthea's patient UUID, the same in the CCDA
     *        (patientRole/id/@extension) and the FHIR file name.
     * @param string $sex FHIR administrative gender (male, female, ...).
     * @param ?string $maritalStatus FHIR v3 MaritalStatus code (M, S, D, W, ...).
     * @param ?string $payer Payer on the patient's most recent Synthea claim;
     *        null when Synthea gave none or "NO_INSURANCE".
     * @param ?PostalAddress $address home address from the FHIR file; its
     *        state is a two-letter code
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
        public ?PostalAddress $address = null,
    ) {
    }

    public function ageOn(\DateTimeImmutable $date): int
    {
        return $this->birthDate->diff($date)->y;
    }

    public function gender(): ?Gender
    {
        return match ($this->sex) {
            'male' => Gender::Male,
            'female' => Gender::Female,
            default => null,
        };
    }

    /**
     * @return array{synthea_id: string, ccda_file: string, fhir_file: string, first_name: string, last_name: string, birth_date: string, sex: string, marital_status: ?string, payer: ?string, address: ?array{street: string, city: string, state: string, postal_code: string}}
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
            'address' => $this->address === null ? null : [
                'street' => $this->address->street,
                'city' => $this->address->city,
                'state' => $this->address->state,
                'postal_code' => $this->address->postalCode,
            ],
        ];
    }

    /**
     * Reads a manifest row written by toArray().
     *
     * @param array<mixed> $row
     */
    public static function fromArray(array $row): self
    {
        $string = static function (string $key) use ($row): string {
            $value = $row[$key] ?? null;
            if (!is_string($value) || $value === '') {
                throw new SyntheaException('Manifest row is missing ' . $key);
            }
            return $value;
        };
        $optional = static function (string $key) use ($row): ?string {
            $value = $row[$key] ?? null;
            return is_string($value) && $value !== '' ? $value : null;
        };
        $birthDate = \DateTimeImmutable::createFromFormat('!Y-m-d', $string('birth_date'), new \DateTimeZone('UTC'));
        if ($birthDate === false) {
            throw new SyntheaException('Manifest row has a bad birth_date');
        }
        $address = null;
        $addressRow = $row['address'] ?? null;
        if (is_array($addressRow)) {
            $parts = [];
            foreach (['street', 'city', 'state', 'postal_code'] as $key) {
                $value = $addressRow[$key] ?? null;
                $parts[$key] = is_string($value) ? $value : '';
            }
            $address = new PostalAddress($parts['street'], $parts['city'], $parts['state'], $parts['postal_code']);
        }
        return new self(
            $string('synthea_id'),
            $string('ccda_file'),
            $string('fhir_file'),
            $string('first_name'),
            $string('last_name'),
            $birthDate,
            $string('sex'),
            $optional('marital_status'),
            $optional('payer'),
            $address,
        );
    }
}
