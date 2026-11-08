<?php

/**
 * A Provenance search runs every US Core service with the request's _lastUpdated and keeps only the Provenance
 * records they return. A value that didn't parse still has to reach the controller's 400, so the result keeps the
 * services' syntax-error mark; a valid search's records, and a service's other rejections, are kept as before.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPatient;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirSearchSyntaxError;
use OpenEMR\Services\FHIR\IResourceReadableService;
use OpenEMR\Services\FHIR\Utils\FhirServiceLocator;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchParameterSyntaxException;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;

class FhirProvenanceSearchSyntaxErrorTest extends TestCase
{
    public function testASyntaxErrorInAServiceMarksTheProvenanceSearch(): void
    {
        $result = $this->provenanceSearchingA($this->recorded(new SearchParameterSyntaxException('_lastUpdated', 'Invalid date')))
            ->getAll(['_lastUpdated' => 'not-a-date']);

        $this->assertSame('_lastUpdated', FhirSearchSyntaxError::find($result));
        $this->assertSame([], $result->getData());
    }

    public function testAServiceRejectingTheParameterOtherwiseIsIgnoredAsBefore(): void
    {
        $result = $this->provenanceSearchingA($this->recorded(new SearchFieldException('_lastUpdated', 'This search field does not exist or is not supported')))
            ->getAll(['_lastUpdated' => 'ge2020-01-01']);

        $this->assertTrue($result->isValid());
        $this->assertNull(FhirSearchSyntaxError::find($result));
    }

    public function testAValidSearchKeepsOnlyTheProvenanceRecordsAsBefore(): void
    {
        $provenance = new FHIRProvenance();
        $records = new ProcessingResult();
        $records->addData(new FHIRPatient());
        $records->addData($provenance);

        $result = $this->provenanceSearchingA($records)->getAll(['_lastUpdated' => 'ge2020-01-01']);

        $this->assertTrue($result->isValid());
        $this->assertSame([$provenance], $result->getData());
    }

    private function recorded(SearchFieldException $exception): ProcessingResult
    {
        $result = new ProcessingResult();
        FhirSearchSyntaxError::record($result, $exception);
        return $result;
    }

    /**
     * A Provenance service whose only US Core service answers every search with $serviceResult.
     */
    private function provenanceSearchingA(ProcessingResult $serviceResult): FhirProvenanceService
    {
        $service = $this->createStub(IResourceReadableService::class);
        $service->method('getAll')->willReturn($serviceResult);
        $locator = $this->createStub(FhirServiceLocator::class);
        $locator->method('findServices')->willReturn(['Patient' => $service]);
        return new FhirProvenanceService(null, $locator);
    }
}
