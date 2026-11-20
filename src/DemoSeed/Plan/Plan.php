<?php

/**
 * Everything the loader will write after the import, worked out before
 * anything is written.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\DemoSeed\Plan;

use OpenEMR\DemoSeed\SeedFile\FacilitySpec;
use OpenEMR\DemoSeed\SeedFile\InsuranceCompanySpec;
use OpenEMR\DemoSeed\SeedFile\ProviderSpec;

final readonly class Plan
{
    /**
     * @param list<FacilitySpec> $facilities
     * @param list<ProviderSpec> $providers
     * @param list<PatientUpdate> $patientUpdates
     * @param list<PlannedPerson> $people
     * @param list<PlannedRelation> $relations
     * @param list<PlannedTelecom> $telecoms
     * @param list<PlannedAddress> $addresses
     * @param array<string, int> $casePatients pid by case patient key
     * @param list<InsuranceCompanySpec> $insuranceCompanies
     * @param list<PlannedPolicy> $policies ended ones before the current
     *        policy of the same type
     * @param list<PlannedAppointment> $appointments
     * @param list<PlannedVisitList> $visitLists
     */
    public function __construct(
        public array $facilities,
        public array $providers,
        public array $patientUpdates,
        public array $people,
        public array $relations,
        public array $telecoms,
        public array $addresses,
        public array $casePatients,
        public array $insuranceCompanies,
        public array $policies,
        public array $appointments,
        public array $visitLists,
    ) {
    }
}
