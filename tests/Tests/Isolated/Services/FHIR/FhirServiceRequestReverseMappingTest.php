<?php

/**
 * Isolated FhirServiceRequestService Reverse Mapping Test
 *
 * Reflection-based tests to verify the private reverse mapping methods
 * (mapFhirStatusToOrderStatus, mapCategoryToOrderType, mapFhirPriorityToOrderPriority)
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

use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\Services\FHIR\FhirServiceRequestService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FhirServiceRequestReverseMappingTest extends TestCase
{
    /** @var \ReflectionClass<FhirServiceRequestService> */
    private ReflectionClass $reflectionClass;
    private FhirServiceRequestService $service;

    protected function setUp(): void
    {
        $this->reflectionClass = new ReflectionClass(FhirServiceRequestService::class);
        $this->service = $this->reflectionClass->newInstanceWithoutConstructor();
    }

    private function invokePrivateMethod(string $methodName, mixed ...$args): mixed
    {
        $method = $this->reflectionClass->getMethod($methodName);
        return $method->invoke($this->service, ...$args);
    }

    // --- mapFhirStatusToOrderStatus tests ---

    public function testMapFhirStatusToOrderStatusActive(): void
    {
        $this->assertSame('pending', $this->invokePrivateMethod('mapFhirStatusToOrderStatus', 'active'));
    }

    public function testMapFhirStatusToOrderStatusCompleted(): void
    {
        $this->assertSame('complete', $this->invokePrivateMethod('mapFhirStatusToOrderStatus', 'completed'));
    }

    public function testMapFhirStatusToOrderStatusRevoked(): void
    {
        $this->assertSame('canceled', $this->invokePrivateMethod('mapFhirStatusToOrderStatus', 'revoked'));
    }

    public function testMapFhirStatusToOrderStatusDraft(): void
    {
        $this->assertSame('draft', $this->invokePrivateMethod('mapFhirStatusToOrderStatus', 'draft'));
    }

    public function testMapFhirStatusToOrderStatusOnHold(): void
    {
        $this->assertSame('on-hold', $this->invokePrivateMethod('mapFhirStatusToOrderStatus', 'on-hold'));
    }

    public function testMapFhirStatusToOrderStatusUnknown(): void
    {
        $this->assertSame('pending', $this->invokePrivateMethod('mapFhirStatusToOrderStatus', 'unknown'));
    }

    public function testMapFhirStatusToOrderStatusDefault(): void
    {
        $this->assertSame('pending', $this->invokePrivateMethod('mapFhirStatusToOrderStatus', 'nonexistent-status'));
    }

    // --- mapCategoryToOrderType tests ---

    private function buildCategory(string $code): FHIRCodeableConcept
    {
        $concept = new FHIRCodeableConcept();
        $coding = new FHIRCoding();
        $coding->setSystem(new FHIRUri('http://snomed.info/sct'));
        $coding->setCode(new FHIRCode($code));
        $concept->addCoding($coding);
        return $concept;
    }

    public function testMapCategoryToOrderTypeLaboratory(): void
    {
        $this->assertSame('laboratory_test', $this->invokePrivateMethod('mapCategoryToOrderType', $this->buildCategory('108252007')));
    }

    public function testMapCategoryToOrderTypeImaging(): void
    {
        $this->assertSame('imaging', $this->invokePrivateMethod('mapCategoryToOrderType', $this->buildCategory('363679005')));
    }

    public function testMapCategoryToOrderTypeClinicalTest(): void
    {
        $this->assertSame('clinical_test', $this->invokePrivateMethod('mapCategoryToOrderType', $this->buildCategory('103693007')));
    }

    public function testMapCategoryToOrderTypeProcedure(): void
    {
        $this->assertSame('procedure', $this->invokePrivateMethod('mapCategoryToOrderType', $this->buildCategory('387713003')));
    }

    public function testMapCategoryToOrderTypeDefault(): void
    {
        $this->assertSame('laboratory_test', $this->invokePrivateMethod('mapCategoryToOrderType', $this->buildCategory('999999999')));
    }

    // --- mapFhirPriorityToOrderPriority tests ---

    public function testMapFhirPriorityToOrderPriorityRoutine(): void
    {
        $this->assertSame('normal', $this->invokePrivateMethod('mapFhirPriorityToOrderPriority', 'routine'));
    }

    public function testMapFhirPriorityToOrderPriorityUrgent(): void
    {
        $this->assertSame('urgent', $this->invokePrivateMethod('mapFhirPriorityToOrderPriority', 'urgent'));
    }

    public function testMapFhirPriorityToOrderPriorityAsap(): void
    {
        $this->assertSame('asap', $this->invokePrivateMethod('mapFhirPriorityToOrderPriority', 'asap'));
    }

    public function testMapFhirPriorityToOrderPriorityStat(): void
    {
        $this->assertSame('stat', $this->invokePrivateMethod('mapFhirPriorityToOrderPriority', 'stat'));
    }

    public function testMapFhirPriorityToOrderPriorityDefault(): void
    {
        $this->assertSame('normal', $this->invokePrivateMethod('mapFhirPriorityToOrderPriority', 'nonexistent-priority'));
    }
}
