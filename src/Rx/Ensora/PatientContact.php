<?php

/**
 * The patient phone numbers sent in Ensora's NCScript PatientContact element.
 *
 * NCScript's ContactType has separate homeTelephone and cellularTelephone
 * elements, so each number goes in its own field rather than one standing in
 * for the other.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

final readonly class PatientContact
{
    public function __construct(
        public ?string $homeTelephone,
        public ?string $cellularTelephone,
    ) {
    }

    /**
     * @param mixed $patient `patient_data` row with phone_home and phone_cell
     */
    public static function fromPatientRow(mixed $patient): self
    {
        return new self(self::phone($patient, 'phone_home'), self::phone($patient, 'phone_cell'));
    }

    private static function phone(mixed $patient, string $key): ?string
    {
        $phone = is_array($patient) ? ($patient[$key] ?? null) : null;
        if (!is_string($phone)) {
            return null;
        }
        $phone = trim(str_replace('-', '', $phone));

        return $phone === '' ? null : $phone;
    }
}
