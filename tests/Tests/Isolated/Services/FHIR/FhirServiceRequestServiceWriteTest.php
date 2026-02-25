<?php

/**
 * Isolated FhirServiceRequestService Write Test
 *
 * Reflection-based tests to verify the write API contract
 * (parseFhirResource, insertOpenEMRRecord, updateOpenEMRRecord)
 * without requiring a database connection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Joshua Baiad <jbaiad@users.noreply.github.com>
 * @copyright Copyright (c) 2026 Joshua Baiad <jbaiad@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\FHIR;

require_once __DIR__ . '/../ProcedureServiceBootstrap.php';

use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\FhirServiceRequestService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

class FhirServiceRequestServiceWriteTest extends TestCase
{
    /** @var \ReflectionClass<FhirServiceRequestService> */
    private ReflectionClass $reflectionClass;

    protected function setUp(): void
    {
        $this->reflectionClass = new ReflectionClass(FhirServiceRequestService::class);
    }

    public function testParseFhirResourceMethodExists(): void
    {
        $this->assertTrue(
            $this->reflectionClass->hasMethod('parseFhirResource'),
            'FhirServiceRequestService must have a parseFhirResource() method'
        );

        $method = $this->reflectionClass->getMethod('parseFhirResource');
        $this->assertTrue($method->isPublic(), 'parseFhirResource() must be public');
    }

    public function testParseFhirResourceAcceptsFhirDomainResource(): void
    {
        $method = $this->reflectionClass->getMethod('parseFhirResource');
        $params = $method->getParameters();
        $this->assertCount(1, $params, 'parseFhirResource() must accept exactly 1 parameter');
        $this->assertSame('fhirResource', $params[0]->getName());

        $type = $params[0]->getType();
        $this->assertNotNull($type, 'parseFhirResource() parameter must be typed');
        $this->assertInstanceOf(ReflectionNamedType::class, $type);
        $this->assertSame(FHIRDomainResource::class, $type->getName());
    }

    public function testInsertOpenEMRRecordMethodExists(): void
    {
        $this->assertTrue(
            $this->reflectionClass->hasMethod('insertOpenEMRRecord'),
            'FhirServiceRequestService must have an insertOpenEMRRecord() method'
        );
    }

    public function testUpdateOpenEMRRecordMethodExists(): void
    {
        $this->assertTrue(
            $this->reflectionClass->hasMethod('updateOpenEMRRecord'),
            'FhirServiceRequestService must have an updateOpenEMRRecord() method'
        );

        $method = $this->reflectionClass->getMethod('updateOpenEMRRecord');
        $this->assertTrue($method->isPublic(), 'updateOpenEMRRecord() must be public');
    }

    public function testTraitNotUsed(): void
    {
        $traits = $this->reflectionClass->getTraitNames();
        $this->assertNotContains(
            FhirServiceBaseEmptyTrait::class,
            $traits,
            'FhirServiceRequestService must NOT use FhirServiceBaseEmptyTrait (real implementations required)'
        );
    }

    public function testReverseMappingMethodsExist(): void
    {
        $this->assertTrue(
            $this->reflectionClass->hasMethod('mapFhirStatusToOrderStatus'),
            'FhirServiceRequestService must have mapFhirStatusToOrderStatus()'
        );
        $this->assertTrue(
            $this->reflectionClass->hasMethod('mapCategoryToOrderType'),
            'FhirServiceRequestService must have mapCategoryToOrderType()'
        );
        $this->assertTrue(
            $this->reflectionClass->hasMethod('mapFhirPriorityToOrderPriority'),
            'FhirServiceRequestService must have mapFhirPriorityToOrderPriority()'
        );
    }
}
