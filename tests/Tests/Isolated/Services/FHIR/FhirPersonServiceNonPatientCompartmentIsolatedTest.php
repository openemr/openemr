<?php

/*
 * FhirPersonServiceNonPatientCompartmentIsolatedTest.php
 *
 * Locks the Person compartment posture. FHIR Person can in principle bridge
 * to Patient / Practitioner / RelatedPerson, but OpenEMR's current
 * implementation is backed by the `users` table only. Marking the service
 * as IPatientCompartmentResourceService would cause the base compartment
 * check to return zero rows to patient tokens, blocking legitimate reads
 * such as care-team provider lookups and RelatedPerson public info. Until
 * the service is extended to source patient records and to scope
 * field-level exposure by caller type, the safe posture is to declare it
 * INonPatientCompartmentResourceService so the base check permits reads
 * and no puuid bind is silently applied.
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
    public function testFhirPersonServiceIsDeclaredNonPatientCompartment(): void
    {
        $interfaces = class_implements(FhirPersonService::class);
        $this->assertIsArray($interfaces);
        $this->assertContains(
            INonPatientCompartmentResourceService::class,
            $interfaces,
            'FhirPersonService must implement INonPatientCompartmentResourceService: it is backed by the users table only and must not deny patient-token reads of provider Person records.'
        );
    }

    public function testFhirPersonServiceDoesNotImplementPatientCompartmentInterface(): void
    {
        $interfaces = class_implements(FhirPersonService::class);
        $this->assertIsArray($interfaces);
        $this->assertNotContains(
            IPatientCompartmentResourceService::class,
            $interfaces,
            'FhirPersonService must NOT implement IPatientCompartmentResourceService while the underlying data source is users-only -- that combination causes the base check to deny and blocks legitimate reads. Extend the service to source patient records before adding the compartment marker.'
        );
    }
}
