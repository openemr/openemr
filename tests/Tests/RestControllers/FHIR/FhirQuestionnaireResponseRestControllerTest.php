<?php

/*
 * FhirQuestionnaireResponseRestControllerTest.php
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
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\RestControllers\FHIR\FhirQuestionnaireResponseRestController;
use OpenEMR\RestControllers\FHIR\FhirQuestionnaireRestController;
use OpenEMR\Services\FHIR\FhirQuestionnaireResponseService;
use OpenEMR\Services\FHIR\FhirQuestionnaireService;
use OpenEMR\Validators\ProcessingResult;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\Exception;
use Symfony\Component\HttpFoundation\Response;

class FhirQuestionnaireResponseRestControllerTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function test__construct(): void
    {
        $resourceService = $this->createMock(FhirQuestionnaireResponseService::class);
        $controller = new FhirQuestionnaireResponseRestController($resourceService);
        $this->assertSame($resourceService, $controller->getFhirQuestionnaireResponseService());
    }

    public function testCreate(): void
    {
        $this->markTestIncomplete("update is not exposed yet, so leaving test as incomplete until we choose to expose it");
    }

    public function testList(): void
    {
        $restRequest = $this->createMock(HttpRestRequest::class);
        $fhirResponse = new FHIRQuestionnaireResponse();
        $fhirResponse->setId(new FHIRId("example-questionnaireresponse-id"));

        $processingResult = $this->createMock(ProcessingResult::class);
        $processingResult->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([
                $fhirResponse
            ]);
        $fhirService = $this->createMock(FhirQuestionnaireResponseService::class);
        $fhirService->expects($this->once())
            ->method('getAll')
            ->willReturn($processingResult);
        $controller = new FhirQuestionnaireResponseRestController($fhirService);
        $response = $controller->list($restRequest, $fhirResponse->getId()->getValue());


        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents, "Response body should not be empty");
        $jsonBundle = json_decode($contents, true);
        $this->assertArrayHasKey("resourceType", $jsonBundle, "Response should contain 'resourceType' key");
        $this->assertEquals('Bundle', $jsonBundle['resourceType'], "Response 'resourceType' should be 'Bundle'");
        $this->assertArrayHasKey("entry", $jsonBundle, "Response should contain 'entry' key");
        $this->assertCount(1, $jsonBundle['entry'], "Response 'entry' should contain one item");
        $this->assertArrayHasKey("resource", $jsonBundle['entry'][0], "'entry' should contain 'resource' key");
        $this->assertArrayHasKey("resourceType", $jsonBundle['entry'][0]['resource'], "Response should contain 'resourceType' key");
        $this->assertEquals("QuestionnaireResponse", $jsonBundle['entry'][0]['resource']['resourceType'], "Response 'resourceType' should be 'QuestionnaireResponse'");
        $this->assertEquals($fhirResponse->getId()->getValue(), $jsonBundle['entry'][0]['resource']['id'], "The id of the returned response should match");
    }

    public function testOne(): void
    {
        $restRequest = $this->createMock(HttpRestRequest::class);
        $restRequest->expects($this->once())
            ->method('getPatientUUIDString')
            ->willReturn('some-uuid-string');

        $fhirResponse = new FHIRQuestionnaireResponse();
        $fhirResponse->setId(new FHIRId("example-questionnaireresponse-id"));

        $processingResult = $this->createMock(ProcessingResult::class);
        $processingResult->expects($this->atLeastOnce())
            ->method('getData')
            ->willReturn([
                $fhirResponse
            ]);
        $processingResult->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $fhirService = $this->createMock(FhirQuestionnaireResponseService::class);
        $fhirService->expects($this->once())
            ->method('getOne')
            ->willReturn($processingResult);
        $controller = new FhirQuestionnaireResponseRestController($fhirService);
        $response = $controller->one($restRequest, $fhirResponse->getId()->getValue());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $contents = $response->getBody()->getContents();
        $this->assertNotEmpty($contents, "Response body should not be empty");
        $jsonBundle = json_decode($contents, true);
        $this->assertArrayHasKey("resourceType", $jsonBundle, "Response should contain 'resourceType' key");
        $this->assertEquals($fhirResponse->get_fhirElementName(), $jsonBundle['resourceType'], "Response 'resourceType' should be 'QuestionnaireResponse'");
        $this->assertEquals($fhirResponse->getId()->getValue(), $jsonBundle['id'], "The id of the returned response should match");
    }
}
