<?php

namespace OpenEMR\Tests\Services\FHIR;

use OpenEMR\FHIR\Export\ExportJob;
use OpenEMR\FHIR\Export\ExportStreamWriter;
use OpenEMR\Services\FHIR\FhirConditionService;
use OpenEMR\Services\FHIR\FhirEncounterService;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for FhirBulkExportDomainResourceTrait patient compartment filtering
 *
 * Validates that services using the trait correctly apply patient UUID filtering
 * for both EXPORT_OPERATION_GROUP and EXPORT_OPERATION_PATIENT types.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rohith Vangalla <rohith.vangalla@optum.com>
 * @copyright Copyright (c) 2026 Rohith Vangalla
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FhirBulkExportDomainResourceTraitTest extends TestCase
{
    private array $testPatientUuids = [
        '90000000-0000-0000-0000-000000000001',
        '90000000-0000-0000-0000-000000000002'
    ];

    /**
     * Test that EXPORT_OPERATION_PATIENT triggers patient compartment filtering
     * for services implementing IPatientCompartmentResourceService
     */
    #[Test]
    public function testPatientExportAppliesCompartmentFiltering(): void
    {
        // Use FhirConditionService as representative patient compartment service
        $service = $this->createPartialMock(FhirConditionService::class, ['getAll']);
        $service->setSystemLogger($this->createMock(LoggerInterface::class));

        // Create export job with PATIENT type
        $job = $this->createMock(ExportJob::class);
        $job->method('getExportType')->willReturn(ExportJob::EXPORT_OPERATION_PATIENT);
        $job->method('getPatientUuidsToExport')->willReturn($this->testPatientUuids);
        $job->method('getResourceIncludeSearchParamValue')->willReturn('gt2026-01-01');

        $writer = $this->createMock(ExportStreamWriter::class);

        // Assert that getAll() is called with patient filter
        $service->expects($this->once())
            ->method('getAll')
            ->with($this->callback(function ($searchParams) {
                // Verify patient UUIDs are in search params
                $this->assertArrayHasKey('patient', $searchParams);
                $this->assertStringContainsString('90000000-0000-0000-0000-000000000001', $searchParams['patient']);
                $this->assertStringContainsString('90000000-0000-0000-0000-000000000002', $searchParams['patient']);
                return true;
            }))
            ->willReturn(new ProcessingResult());

        // Execute export
        $service->export($writer, $job);
    }

    /**
     * Test that EXPORT_OPERATION_GROUP still works as before
     */
    #[Test]
    public function testGroupExportAppliesCompartmentFiltering(): void
    {
        $service = $this->createPartialMock(FhirEncounterService::class, ['getAll']);
        $service->setSystemLogger($this->createMock(LoggerInterface::class));

        $job = $this->createMock(ExportJob::class);
        $job->method('getExportType')->willReturn(ExportJob::EXPORT_OPERATION_GROUP);
        $job->method('getPatientUuidsToExport')->willReturn($this->testPatientUuids);
        $job->method('getResourceIncludeSearchParamValue')->willReturn('gt2026-01-01');

        $writer = $this->createMock(ExportStreamWriter::class);

        $service->expects($this->once())
            ->method('getAll')
            ->with($this->callback(function ($searchParams) {
                $this->assertArrayHasKey('patient', $searchParams);
                return true;
            }))
            ->willReturn(new ProcessingResult());

        $service->export($writer, $job);
    }

    /**
     * Test early return when no patient UUIDs provided (prevents empty export files)
     */
    #[Test]
    public function testPatientExportReturnsEarlyWhenNoPatientUuids(): void
    {
        $service = $this->createPartialMock(FhirConditionService::class, ['getAll']);
        $service->setSystemLogger($this->createMock(LoggerInterface::class));

        $job = $this->createMock(ExportJob::class);
        $job->method('getExportType')->willReturn(ExportJob::EXPORT_OPERATION_PATIENT);
        $job->method('getPatientUuidsToExport')->willReturn([]); // Empty patient list
        $job->method('getResourceIncludeSearchParamValue')->willReturn('gt2026-01-01');

        $writer = $this->createMock(ExportStreamWriter::class);

        // getAll() should NOT be called when no patients
        $service->expects($this->never())->method('getAll');

        $service->export($writer, $job);
    }

    /**
     * Test that EXPORT_OPERATION_SYSTEM does not apply patient compartment filtering
     */
    #[Test]
    public function testSystemExportDoesNotApplyCompartmentFiltering(): void
    {
        $service = $this->createPartialMock(FhirConditionService::class, ['getAll']);
        $service->setSystemLogger($this->createMock(LoggerInterface::class));

        $job = $this->createMock(ExportJob::class);
        $job->method('getExportType')->willReturn(ExportJob::EXPORT_OPERATION_SYSTEM);
        $job->method('getPatientUuidsToExport')->willReturn($this->testPatientUuids);
        $job->method('getResourceIncludeSearchParamValue')->willReturn('gt2026-01-01');

        $writer = $this->createMock(ExportStreamWriter::class);

        // getAll() should be called WITHOUT patient filter for system export
        $service->expects($this->once())
            ->method('getAll')
            ->with($this->callback(function ($searchParams) {
                // Patient filter should NOT be present for system export
                $this->assertArrayNotHasKey('patient', $searchParams);
                return true;
            }))
            ->willReturn(new ProcessingResult());

        $service->export($writer, $job);
    }

    /**
     * Test that patient UUIDs are correctly formatted as comma-separated string
     */
    #[Test]
    public function testPatientUuidsFormattedCorrectly(): void
    {
        $service = $this->createPartialMock(FhirConditionService::class, ['getAll']);
        $service->setSystemLogger($this->createMock(LoggerInterface::class));

        $job = $this->createMock(ExportJob::class);
        $job->method('getExportType')->willReturn(ExportJob::EXPORT_OPERATION_PATIENT);
        $job->method('getPatientUuidsToExport')->willReturn($this->testPatientUuids);
        $job->method('getResourceIncludeSearchParamValue')->willReturn('gt2026-01-01');

        $writer = $this->createMock(ExportStreamWriter::class);

        $service->expects($this->once())
            ->method('getAll')
            ->with($this->callback(function ($searchParams) {
                $expected = implode(',', $this->testPatientUuids);
                $this->assertEquals($expected, $searchParams['patient']);
                return true;
            }))
            ->willReturn(new ProcessingResult());

        $service->export($writer, $job);
    }
}
