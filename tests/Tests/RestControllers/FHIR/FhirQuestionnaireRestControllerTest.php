<?php

/*
 * FhirQuestionnaireRestControllerTest.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\RestControllers\FHIR;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\RestControllers\FHIR\FhirQuestionnaireRestController;
use OpenEMR\Services\FHIR\FhirQuestionnaireService;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\MockObject\Exception;

class FhirQuestionnaireRestControllerTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function test__construct(): void
    {
        $logger = $this->createMock(SystemLogger::class);
        $resourceService = $this->createMock(FhirQuestionnaireService::class);
        $controller = new FhirQuestionnaireRestController($logger, $resourceService);
        $this->assertSame($logger, $controller->getSystemLogger());
        $this->assertSame($resourceService, $controller->getFhirQuestionnaireService());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testOne(): void
    {
        $logger = $this->createMock(SystemLogger::class);
        $resourceService = $this->createMock(FhirQuestionnaireService::class);
        $restRequest = $this->createMock(HttpRestRequest::class);
        $restRequest->expects($this->once())
            ->method('isPatientRequest')
            ->willReturn(true);
        $restRequest->expects($this->once())
            ->method('getPatientUUIDString')
            ->willReturn('some-uuid-string');

        $fhirQuestionnaire = new FHIRQuestionnaire();
        $fhirQuestionnaire->setId(new FHIRId("example-questionnaire-id"));

        $processingResult = $this->createMock(ProcessingResult::class);
        $processingResult->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([
                $fhirQuestionnaire
            ]);
        $processingResult->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $resourceService->expects($this->once())
            ->method('getOne')
            ->willReturn($processingResult);
        $controller = new FhirQuestionnaireRestController($logger, $resourceService);
        $response = $controller->one($restRequest, $fhirQuestionnaire->getId()->getValue());


        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents, "Response body should not be empty");
        $jsonBundle = json_decode($contents, true);
        $this->assertArrayHasKey("resourceType", $jsonBundle, "Response should contain 'resourceType' key");
        $this->assertEquals("Questionnaire", $jsonBundle['resourceType'], "Response 'resourceType' should be 'Questionnaire'");
        $this->assertEquals("example-questionnaire-id", $jsonBundle['id'], "The id of the returned questionnaire should match");
    }

    public function testUpdate(): void
    {
        $this->markTestIncomplete("update is not exposed yet, so leaving test as incomplete until we choose to expose it");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testList(): void
    {

        $logger = $this->createMock(SystemLogger::class);
        $resourceService = $this->createMock(FhirQuestionnaireService::class);
        $restRequest = $this->createMock(HttpRestRequest::class);
        $restRequest->expects($this->once())
            ->method('isPatientRequest')
            ->willReturn(true);
        $restRequest->expects($this->once())
            ->method('getQueryParams')
            ->willReturn([]);
        $restRequest->expects($this->once())
            ->method('getPatientUUIDString')
            ->willReturn('some-uuid-string');

        $fhirQuestionnaire = new FHIRQuestionnaire();
        $fhirQuestionnaire->setId(new FhirId("example-questionnaire-id"));

        $processingResult = $this->createMock(ProcessingResult::class);
        $processingResult->expects($this->once())
            ->method('getData')
            ->willReturn([
                $fhirQuestionnaire
            ]);
        $resourceService->expects($this->once())
            ->method('getAll')
            ->willReturn($processingResult);
        $controller = new FhirQuestionnaireRestController($logger, $resourceService);
        $response = $controller->list($restRequest);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents, "Response body should not be empty");
        $jsonBundle = json_decode($contents, true);
        $this->assertArrayHasKey("entry", $jsonBundle, "Response should contain 'entry' key");
        $this->assertCount(1, $jsonBundle['entry'], "Response 'entry' should contain one item");
        $this->assertArrayHasKey("resource", $jsonBundle['entry'][0], "'entry' should contain 'resource' key");
        $this->assertArrayHasKey("resourceType", $jsonBundle['entry'][0]['resource'], "Response should contain 'resourceType' key");
        $this->assertEquals("Questionnaire", $jsonBundle['entry'][0]['resource']['resourceType'], "Response 'resourceType' should be 'Questionnaire'");
        $this->assertEquals($fhirQuestionnaire->getId()->getValue(), $jsonBundle['entry'][0]['resource']['id'], "The id of the returned questionnaire should match");
    }

    public function testCreate(): void
    {
        $this->markTestIncomplete("create is not exposed yet, so leaving test as incomplete until we choose to expose it");
    }
}
