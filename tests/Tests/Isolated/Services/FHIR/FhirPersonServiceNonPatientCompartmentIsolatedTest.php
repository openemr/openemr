<?php

/*
 * FhirPersonServicePatientDenyIsolatedTest.php
 *
 * Locks the Person compartment posture on {@see FhirPersonService}. The
 * service is backed by the `users` table (staff only — patients live in
 * `patient_data` and never appear here), and Person is not a US Core
 * profile. Patient callers reach ONC-shaped provider-directory
 * information through `/Practitioner` and `/PractitionerRole` instead.
 *
 * Class-level posture: FhirPersonService declares neither
 * IPatientCompartmentResourceService (there is no patient data in the
 * users table for the base class to bind against) nor
 * INonPatientCompartmentResourceService (that marker's semantics drop
 * the puuid bind and return unscoped rows to patient callers). Absent
 * both markers, {@see FhirServiceBase::assertPatientCompartmentBind()}
 * returns false for any patient-scoped call and the service returns a
 * denied ProcessingResult. Route-layer denies (see
 * `FhirRouteAclEnforcementIsolatedTest`) sit in front of this as a
 * second gate with clearer error semantics.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\Services\FHIR\FhirPersonService;
use OpenEMR\Services\FHIR\INonPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use PHPUnit\Framework\TestCase;

class FhirPersonServiceNonPatientCompartmentIsolatedTest extends TestCase
{
    public function testFhirPersonServiceDoesNotImplementPatientCompartmentInterface(): void
    {
        $interfaces = class_implements(FhirPersonService::class);
        $this->assertIsArray($interfaces);
        $this->assertNotContains(
            IPatientCompartmentResourceService::class,
            $interfaces,
            'FhirPersonService is backed by the users table only (staff records); it must not declare IPatientCompartmentResourceService — there is no patient-data column for the base class to bind against.'
        );
    }

    public function testFhirPersonServiceDoesNotImplementNonPatientCompartmentInterface(): void
    {
        // INonPatientCompartmentResourceService's semantics are "drop the
        // patient bind and return unscoped rows to patient callers." That
        // is the opposite of what Person needs — patients have no
        // legitimate read path here. Without either marker the base class
        // fail-closed branch returns a denied ProcessingResult for any
        // patient-scoped call, which is the intended shape.
        $interfaces = class_implements(FhirPersonService::class);
        $this->assertIsArray($interfaces);
        $this->assertNotContains(
            INonPatientCompartmentResourceService::class,
            $interfaces,
            'FhirPersonService must NOT declare INonPatientCompartmentResourceService — that marker drops the patient bind and returns unscoped rows to patient callers, which is exactly the shape being closed off here.'
        );
    }
}
