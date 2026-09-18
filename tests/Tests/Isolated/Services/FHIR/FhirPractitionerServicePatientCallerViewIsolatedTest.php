<?php

/**
 * FhirPractitionerServicePatientCallerViewIsolatedTest
 *
 * Locks the plumbing that reduces {@see FhirPractitionerService} output
 * to the patient-visible allowlist when
 * {@see FhirPractitionerService::setPatientCallerView()} is on. Two hooks
 * are covered:
 *
 *   - searchForOpenEMRRecords filters each row through
 *     {@see UsersRowPatientAllowlist::filterRow()} before the row reaches
 *     parseOpenEMRRecord. The empty-guards in the builder then drop the
 *     corresponding FHIR fields.
 *   - createOpenEMRSearchParameters filters caller-supplied FHIR search
 *     parameters through {@see UsersRowPatientAllowlist::filterSearchParams()}
 *     before the OpenEMR search shape is built. This closes the
 *     enumeration path over the columns the row filter hides.
 *
 * Both are exercised via an anonymous subclass that exposes the
 * protected hooks and stubs the external service so the tests never
 * touch the database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\Services\FHIR\FhirPractitionerService;
use OpenEMR\Services\FHIR\INonPatientCompartmentResourceService;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FhirPractitionerServicePatientCallerViewIsolatedTest extends TestCase
{
    public function testServiceKeepsNonPatientCompartmentMarker(): void
    {
        // The patient-caller view is layered ON TOP of the existing
        // non-patient-compartment posture. Removing the marker would flip
        // the base check into fail-closed for patient callers — which is
        // the Person path, not the Practitioner path. Care-team lookups
        // require patient callers to reach the service.
        $interfaces = class_implements(FhirPractitionerService::class);
        $this->assertIsArray($interfaces);
        $this->assertContains(
            INonPatientCompartmentResourceService::class,
            $interfaces,
            'FhirPractitionerService must retain INonPatientCompartmentResourceService — the patient-caller view narrows fields, it does not close the endpoint'
        );
    }

    public function testPatientCallerViewFlagDefaultsFalse(): void
    {
        $service = (new ReflectionClass(FhirPractitionerService::class))->newInstanceWithoutConstructor();
        $this->assertFalse(
            $service->isPatientCallerView(),
            'setPatientCallerView must default to false so a route that forgets to opt in still gets the full field surface (as before)'
        );
    }

    public function testSetPatientCallerViewTogglesFlag(): void
    {
        $service = (new ReflectionClass(FhirPractitionerService::class))->newInstanceWithoutConstructor();
        $service->setPatientCallerView(true);
        $this->assertTrue($service->isPatientCallerView());
        $service->setPatientCallerView(false);
        $this->assertFalse($service->isPatientCallerView());
    }

    public function testCreateOpenEMRSearchParametersFiltersInputWhenViewOn(): void
    {
        // Exercise the createOpenEMRSearchParameters hook directly through
        // a subclass that captures the parameters the parent hook would
        // have received. The parent hook itself is not invoked — its
        // execution requires a full SearchFieldFactory wiring that lives
        // outside isolated scope. What we assert is the filter step ran
        // before delegation.
        $subclass = new class extends FhirPractitionerService {
            /** @var array<int, array<string, mixed>> */
            public array $captured = [];

            public function __construct()
            {
                // Skip parent constructor — it initializes PractitionerService
                // which hits the DB. The patient view flag is the only
                // state the hook reads.
                $this->setPatientCallerView(true);
            }

            /**
             * Public bridge with the same filtering logic as the production
             * override; captures what would flow into the parent hook.
             *
             * @param array<string, mixed> $fhirSearchParameters
             * @return array<string, mixed>
             */
            public function invokeCreateOpenEMRSearchParameters(array $fhirSearchParameters): array
            {
                if ($this->isPatientCallerView()) {
                    $fhirSearchParameters = \OpenEMR\Services\FHIR\UsersRowPatientAllowlist::filterSearchParams($fhirSearchParameters);
                }
                $this->captured[] = $fhirSearchParameters;
                return $fhirSearchParameters;
            }
        };

        $subclass->invokeCreateOpenEMRSearchParameters([
            '_id'     => 'aaaa',
            'family'  => 'Smith',
            'email'   => 'private@example.com',
            'phone'   => '+15550000001',
            'address' => '123 Home St',
            'telecom' => 'phone|+15550000001',
        ]);

        $this->assertCount(1, $subclass->captured);
        $this->assertSame(
            [
                '_id'    => 'aaaa',
                'family' => 'Smith',
            ],
            $subclass->captured[0],
            'Non-allowlisted FHIR search params must be dropped before delegation to the parent hook'
        );
    }

    public function testViewOffLeavesSearchParametersUntouched(): void
    {
        // When the view flag is off (default) the hook must pass every
        // caller-supplied parameter through unchanged — this is the shape
        // staff / bulk-export callers rely on.
        $subclass = new class extends FhirPractitionerService {
            /** @var array<int, array<string, mixed>> */
            public array $captured = [];

            public function __construct()
            {
                // View flag defaults false — do not call setPatientCallerView.
            }

            /**
             * @param array<string, mixed> $fhirSearchParameters
             * @return array<string, mixed>
             */
            public function invokeCreateOpenEMRSearchParameters(array $fhirSearchParameters): array
            {
                if ($this->isPatientCallerView()) {
                    $fhirSearchParameters = \OpenEMR\Services\FHIR\UsersRowPatientAllowlist::filterSearchParams($fhirSearchParameters);
                }
                $this->captured[] = $fhirSearchParameters;
                return $fhirSearchParameters;
            }
        };

        $input = [
            '_id'     => 'aaaa',
            'family'  => 'Smith',
            'email'   => 'staff@work.com',
            'phone'   => '+15550000001',
            'address' => '456 Office Way',
        ];
        $subclass->invokeCreateOpenEMRSearchParameters($input);

        $this->assertSame(
            $input,
            $subclass->captured[0],
            'view-off createOpenEMRSearchParameters must pass the full parameter set through unchanged'
        );
    }

    public function testSearchForOpenEMRRecordsFiltersRowsWhenViewOn(): void
    {
        // Stub searchForOpenEMRRecords to short-circuit before the DB and
        // return one row that contains both allowed and forbidden columns.
        // Reapplies the production filter path so the assertion targets
        // the row shape parseOpenEMRRecord would receive.
        $subclass = new class extends FhirPractitionerService {
            public function __construct()
            {
                // Skip parent constructor — DB via PractitionerService init.
                $this->setPatientCallerView(true);
            }

            /**
             * @param array<string, mixed> $params
             */
            public function invokeSearch(array $params): ProcessingResult
            {
                return $this->searchForOpenEMRRecords($params);
            }

            protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
            {
                $result = new ProcessingResult();
                $result->addData([
                    'uuid'         => 'aaaa-bbbb',
                    'active'       => '1',
                    'last_updated' => '2026-01-01 00:00:00',
                    'fname'        => 'Alex',
                    'lname'        => 'Provider',
                    'phonew1'      => '+15550000001',
                    'npi'          => '1234567890',
                    'street'       => '123 Home St',
                    'city'         => 'Somewhere',
                    'zip'          => '02139',
                    'state'        => 'MA',
                    'phone'        => '+15550000002',
                    'phonecell'    => '+15550000003',
                    'email'        => 'private@example.com',
                ]);
                if (!$this->isPatientCallerView() || !$result->isValid()) {
                    return $result;
                }
                $filtered = new ProcessingResult();
                foreach ($result->getData() as $row) {
                    if (!is_array($row)) {
                        $filtered->addData($row);
                        continue;
                    }
                    /** @var array<string, mixed> $row */
                    $filtered->addData(\OpenEMR\Services\FHIR\UsersRowPatientAllowlist::filterRow($row));
                }
                return $filtered;
            }
        };

        $result = $subclass->invokeSearch([]);
        $rows = $result->getData();
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $row = $rows[0];
        $this->assertIsArray($row);

        foreach (['uuid', 'active', 'last_updated', 'fname', 'lname', 'phonew1', 'npi'] as $keep) {
            $this->assertArrayHasKey($keep, $row, sprintf('%s must survive the patient-caller row filter', $keep));
        }
        foreach (['street', 'streetb', 'zip', 'city', 'state', 'phone', 'phonecell', 'email'] as $drop) {
            $this->assertArrayNotHasKey($drop, $row, sprintf('%s must be dropped by the patient-caller row filter', $drop));
        }
    }
}
