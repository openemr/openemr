<?php

declare(strict_types=1);

/*
 * FhirQuestionnaireResponseServicePatientCompartmentIsolatedTest.php
 *
 * Previously, FhirQuestionnaireResponseService and
 * FhirQuestionnaireResponseFormService defined the `patient` search field
 * via PatientSearchTrait but did NOT implement
 * IPatientCompartmentResourceService, so the enforcement gate in
 * FhirServiceBase::getOne() silently dropped the puuid bind. This test
 * locks in the marker interface — its absence lets another patient's rows
 * be returned.
 *
 * @package   openemr
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\Services\FHIR\FhirQuestionnaireResponseService;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use PHPUnit\Framework\TestCase;

class FhirQuestionnaireResponseServicePatientCompartmentIsolatedTest extends TestCase
{
    public function testFhirQuestionnaireResponseServiceImplementsPatientCompartmentInterface(): void
    {
        $interfaces = class_implements(FhirQuestionnaireResponseService::class);
        $this->assertIsArray($interfaces);
        $this->assertContains(
            IPatientCompartmentResourceService::class,
            $interfaces,
            'FhirQuestionnaireResponseService must implement IPatientCompartmentResourceService so the base check binds the patient compartment.'
        );
    }

    public function testFhirQuestionnaireResponseFormServiceImplementsPatientCompartmentInterface(): void
    {
        $interfaces = class_implements(FhirQuestionnaireResponseFormService::class);
        $this->assertIsArray($interfaces);
        $this->assertContains(
            IPatientCompartmentResourceService::class,
            $interfaces,
            'FhirQuestionnaireResponseFormService must implement IPatientCompartmentResourceService — the mapped service also carries patient data and must be scoped.'
        );
    }

    public function testFhirQuestionnaireResponseServicePatientContextBindsToPuuid(): void
    {
        $field = $this->getPatientContextSearchFieldWithoutConstructor(FhirQuestionnaireResponseService::class);

        $this->assertSame('patient', $field->getName());
        // The compartment must bind to the puuid column so the where-clause
        // filters rows to the authenticated patient's questionnaire responses.
        $this->assertSame('puuid', $this->firstMappedFieldName($field));
    }

    /**
     * Invokes {@see IPatientCompartmentResourceService::getPatientContextSearchField()}
     * on a service without booting its constructor (which typically requires DB
     * services / legacy library code not available in isolated tests).
     *
     * @param class-string $serviceClass
     */
    private function getPatientContextSearchFieldWithoutConstructor(string $serviceClass): FhirSearchParameterDefinition
    {
        $reflection = new \ReflectionMethod($serviceClass, 'getPatientContextSearchField');
        $stub = (new \ReflectionClass($serviceClass))->newInstanceWithoutConstructor();
        $field = $reflection->invoke($stub);
        $this->assertInstanceOf(
            FhirSearchParameterDefinition::class,
            $field,
            $serviceClass . '::getPatientContextSearchField() must return a FhirSearchParameterDefinition.'
        );
        return $field;
    }

    /**
     * Extracts the name of the first mapped OpenEMR field a FHIR search
     * definition targets. Per the {@see FhirSearchParameterDefinition}
     * contract the mapped-fields accessor returns either an array of
     * ServiceField instances (typed) or a single string (untyped legacy
     * shape) — this helper flattens both.
     */
    private function firstMappedFieldName(FhirSearchParameterDefinition $definition): ?string
    {
        $mappedFields = $definition->getMappedFields();
        if (is_string($mappedFields)) {
            return $mappedFields;
        }
        if ($mappedFields === []) {
            return null;
        }
        return $mappedFields[0]->getField();
    }
}
