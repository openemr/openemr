<?php

/**
 * Isolated tests for SpreadSheetService
 *
 * Tests the SpreadSheetService class functionality without requiring database connections
 * or external dependencies. Validates basic interaction with the SpreadSheet API.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Generated Tests
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\SpreadSheetService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpreadSheetServiceTest extends TestCase
{
    private function createSpreadSheetServiceMock(
        ?array $arrayData = null,
        ?array $fields = null,
        string $fileName = 'test_report',
        bool $isCliReturn = false,
        array $additionalMethods = []
    ): MockObject|SpreadSheetService {
        $defaultData = [
            ['Name' => 'Alice', 'Age' => 30, 'City' => 'New York'],
            ['Name' => 'Bob', 'Age' => 25, 'City' => 'Los Angeles'],
        ];
        $defaultFields = ['Name', 'Age'];

        $methods = array_merge(['isCli'], $additionalMethods);

        $mock = $this->getMockBuilder(SpreadSheetService::class)
            ->setConstructorArgs([
                $arrayData ?? $defaultData,
                $fields ?? $defaultFields,
                $fileName
            ])
            ->onlyMethods($methods)
            ->getMock();

        $mock->method('isCli')->willReturn($isCliReturn);

        return $mock;
    }

    /**
     * When isCli is true, then constructor throws RuntimeException.
     */
    public function testConstructorThrowsExceptionInCliMode(): void
    {
        $this->expectException(\RuntimeException::class);

        // Create a test service that extends SpreadSheetService to override isCli
        $testService = new class ([['Name' => 'Alice']], ['Name']) extends SpreadSheetService {
            protected function isCli(): bool
            {
                return true; // Simulate CLI mode
            }
        };
    }

    /**
     * buildSpreadsheet will return `false` when it is called with empty arrayData.
     */
    public function testBuildSpreadsheetWithNoData(): void
    {
        $emptyDataService = $this->createSpreadSheetServiceMock(arrayData: []);

        // Test that buildSpreadsheet returns false with empty data
        $result = $emptyDataService->buildSpreadsheet();
        $this->assertFalse($result);
    }

    /**
     * buildSpreadsheet populates the `$row` and `$header` properties
     * based on the contents of the $arrayData property.
     *
     * If the $fields property is set, buildSpreadsheet will not change it.
     */
    public function testBuildSpreadsheetPopulatesProperties(): void
    {
        $service = $this->createSpreadSheetServiceMock();

        // Test with fields already set - fields should not change
        $result = $service->buildSpreadsheet();
        $this->assertTrue($result);

        // Use reflection to check internal properties on the actual service class
        $reflection = new \ReflectionClass(SpreadSheetService::class);

        // Check that row property is populated
        $rowProperty = $reflection->getProperty('row');
        $rowData = $rowProperty->getValue($service);
        $this->assertNotEmpty($rowData);
        $this->assertCount(2, $rowData); // Should have 2 data rows

        // Check that header property is populated
        $headerProperty = $reflection->getProperty('header');
        $headerData = $headerProperty->getValue($service);
        $this->assertNotEmpty($headerData);

        // Check that fields property remains unchanged (was set in constructor)
        $fieldsProperty = $reflection->getProperty('fields');
        $fieldsData = $fieldsProperty->getValue($service);
        $this->assertEquals(['Name', 'Age'], $fieldsData);
    }

    /**
     * buildSpreadsheet populates the `$row` and `$header` properties
     * based on the contents of the $arrayData property.
     *
     * If the $fields property is not set, buildSpreadsheet will also populate it
     * based on the keys of the $arrayData property.
     */
    public function testBuildSpreadsheetPopulatesFieldsWhenNotSet(): void
    {
        // Create service with no fields specified (empty array)
        $serviceWithoutFields = $this->createSpreadSheetServiceMock(fields: []);

        $result = $serviceWithoutFields->buildSpreadsheet();
        $this->assertTrue($result);

        // Check that fields property is populated from array keys
        $reflection = new \ReflectionClass(SpreadSheetService::class);
        $fieldsProperty = $reflection->getProperty('fields');
        $fieldsData = $fieldsProperty->getValue($serviceWithoutFields);

        // Fields should be populated with keys from the data array
        $this->assertContains('Name', $fieldsData);
        $this->assertContains('Age', $fieldsData);
        $this->assertContains('City', $fieldsData);
    }

    /**
     * downloadSpreadsheet sets headers and writes the spreadsheet to php://output
     */
    public function testDownloadSpreadsheet(): void
    {
        $mockService = $this->createSpreadSheetServiceMock(
            additionalMethods: ['getActiveSheet', 'setHeaders', 'writeOutput']
        );

        // Initialize the service by calling buildSpreadsheet first
        $mockService->buildSpreadsheet();

        // Mock getActiveSheet to return a mock worksheet object
        $mockSheet = $this->createMock(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::class);
        $mockService->expects($this->once())
            ->method('getActiveSheet')
            ->willReturn($mockSheet);

        // Mock setHeaders to be called once
        $mockService->expects($this->once())
            ->method('setHeaders');

        // Mock writeOutput to be called once
        $mockService->expects($this->once())
            ->method('writeOutput');

        // Call the method under test
        $mockService->downloadSpreadsheet();
    }
}
