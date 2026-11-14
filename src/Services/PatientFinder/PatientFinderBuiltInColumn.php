<?php

/**
 * One of the combined columns the Patient Finder builds itself (see
 * {@see PatientFinderBuiltInColumns}).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientFinder;

/**
 * Backed because the value is the option id an administrator gives a finder column list
 * entry (stored in list_options) and a Patient Finder Detail layout field (layout_options).
 */
enum PatientFinderBuiltInColumn: string
{
    case NameFirstLast = 'name_first_last';
    case NameFirstLastMiddle = 'name_first_last_middle';
    case CityState = 'city_state';
    case AddressFull = 'address_full';
    case AddressFullCountry = 'address_full_country';
    case Age = 'age';
    case NextAppointment = 'next_appointment';
    case LastAppointment = 'last_appointment';
    case InsurancePrimaryProviderName = 'insurance_primary_provider_name';
    case InsurancePrimaryPolicy = 'insurance_primary_policy';

    /**
     * The ACL section and value a user needs to see the column, beyond the finder's own
     * patients/demo, or null when the finder's access is enough. Appointment times need
     * patients/appt, as on the patient summary.
     *
     * @return array{string, string}|null
     */
    public function requiredAcl(): ?array
    {
        return match ($this) {
            self::NextAppointment, self::LastAppointment => ['patients', 'appt'],
            self::NameFirstLast, self::NameFirstLastMiddle, self::CityState, self::AddressFull,
            self::AddressFullCountry, self::Age, self::InsurancePrimaryProviderName,
            self::InsurancePrimaryPolicy => null,
        };
    }

    /**
     * Whether the column's value comes from a correlated subquery on another table, which
     * runs once per patient when it is searched.
     */
    public function usesSubquery(): bool
    {
        return match ($this) {
            self::InsurancePrimaryProviderName, self::InsurancePrimaryPolicy => true,
            // The appointment columns are worked out in PHP and never searched.
            self::NextAppointment, self::LastAppointment, self::NameFirstLast, self::NameFirstLastMiddle,
            self::CityState, self::AddressFull, self::AddressFullCountry, self::Age => false,
        };
    }

    /**
     * Whether the column is a patient's next or last appointment, which
     * PatientFinderAppointments works out after the query.
     */
    public function isAppointment(): bool
    {
        return match ($this) {
            self::NextAppointment, self::LastAppointment => true,
            self::NameFirstLast, self::NameFirstLastMiddle, self::CityState, self::AddressFull,
            self::AddressFullCountry, self::Age, self::InsurancePrimaryProviderName,
            self::InsurancePrimaryPolicy => false,
        };
    }
}
