<?php

namespace OpenEMR\Tests\RestControllers;

use PHPUnit\Framework\TestCase;
use OpenEMR\RestControllers\RestControllerHelper;
use OpenEMR\Validators\ProcessingResult;

class HandleProcessingResultTest extends TestCase
{
    private $processingResult;

    protected function setup(): void
    {
        $this->processingResult = new ProcessingResult();
    }

    /**
     * Tests a processing result with a validation message
     */
    public function testWithValidationMessage(): void
    {
        $this->processingResult->setValidationMessages(["fname" => "bad value"]);
        $actualValue = RestControllerHelper::handleProcessingResult($this->processingResult, 201, false);

        $this->assertEquals(400, http_response_code());
        $this->assertEquals(1, count($actualValue["validationErrors"]));
        $this->assertEquals(0, count($actualValue["internalErrors"]));
        $this->assertEquals(0, count($actualValue["data"]));
    }

    /**
     * Tests a processing result with an internal error
     */
    public function testWithInternalError(): void
    {
        $this->processingResult->addInternalError("internal error occurred");
        $actualValue = RestControllerHelper::handleProcessingResult($this->processingResult, 201, false);

        $this->assertEquals(500, http_response_code());
        $this->assertEquals(0, count($actualValue["validationErrors"]));
        $this->assertEquals(1, count($actualValue["internalErrors"]));
        $this->assertEquals(0, count($actualValue["data"]));
    }

    /**
     * Tests a processing result where a single item response is requested
     */
    public function testWithSingleItemResponse(): void
    {
        $expectedData = ["pid" => 1];
        $this->processingResult->addData($expectedData);

        $actualValue = RestControllerHelper::handleProcessingResult($this->processingResult, 201, false);

        $this->assertEquals(201, http_response_code());
        $this->assertEquals(0, count($actualValue["validationErrors"]));
        $this->assertEquals(0, count($actualValue["internalErrors"]));

        $actualData = $actualValue["data"];
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * Tests a processing result where a multiple item response is requested
     */
    public function testWithMultiItemResponse(): void
    {
        $this->processingResult->addData(["fname" => "John"]);
        $this->processingResult->addData(["fname" => "Jane"]);
        $expectedData = $this->processingResult->getData();

        $actualValue = RestControllerHelper::handleProcessingResult($this->processingResult, 200, true);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(0, count($actualValue["validationErrors"]));
        $this->assertEquals(0, count($actualValue["internalErrors"]));

        $actualData = $actualValue["data"];
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * Tests a processing result with an "empty data set" where a multiple item response is requested.
     */
    public function testWithEmptyMultiItemResponse(): void
    {
        $expectedData = [];

        $actualValue = RestControllerHelper::handleProcessingResult($this->processingResult, 200, true);

        $this->assertEquals(200, http_response_code());
        $this->assertEquals(0, count($actualValue["validationErrors"]));
        $this->assertEquals(0, count($actualValue["internalErrors"]));

        $actualData = $actualValue["data"];
        $this->assertEquals($expectedData, $actualData);
    }
}
