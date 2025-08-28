<?php

/**
 * Isolated TelemetryService Test
 *
 * Tests TelemetryService functionality without database dependencies.
 * Uses stubs and mocks to test business logic in isolation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Telemetry;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UniqueInstallationUuid;
use OpenEMR\Services\VersionServiceInterface;
use OpenEMR\Telemetry\GeoTelemetry;
use OpenEMR\Telemetry\GeoTelemetryInterface;
use OpenEMR\Telemetry\TelemetryRepository;
use OpenEMR\Telemetry\TelemetryService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TelemetryServiceTest extends TestCase
{
    public function testIsTelemetryEnabledReturnsTrueWhenTelemetryDisabledIsZero(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        // Create a partial mock of TelemetryService to mock the fetchRecords method
        $telemetryService = $this->getMockBuilder(TelemetryService::class)
            ->setConstructorArgs([$mockRepository, $mockVersionService, $mockLogger])
            ->onlyMethods(['fetchRecords'])
            ->getMock();

        // Mock fetchRecords to return telemetry_disabled = 0 (enabled)
        $telemetryService->expects($this->once())
            ->method('fetchRecords')
            ->with("SELECT `telemetry_disabled` FROM `product_registration` WHERE `telemetry_disabled` = 0", [])
            ->willReturn([['telemetry_disabled' => 0]]);

        $result = $telemetryService->isTelemetryEnabled();

        $this->assertEquals(1, $result);
    }

    public function testIsTelemetryEnabledReturnsFalseWhenTelemetryDisabledIsNotZero(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        // Create a partial mock of TelemetryService to mock the fetchRecords method
        $telemetryService = $this->getMockBuilder(TelemetryService::class)
            ->setConstructorArgs([$mockRepository, $mockVersionService, $mockLogger])
            ->onlyMethods(['fetchRecords'])
            ->getMock();

        // Mock fetchRecords to return empty result (telemetry disabled)
        $telemetryService->expects($this->once())
            ->method('fetchRecords')
            ->with("SELECT `telemetry_disabled` FROM `product_registration` WHERE `telemetry_disabled` = 0", [])
            ->willReturn([]);

        $result = $telemetryService->isTelemetryEnabled();

        $this->assertEquals(0, $result);
    }

    public function testReportClickEventReturnsErrorForMissingEventType(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        $telemetryService = new TelemetryService($mockRepository, $mockVersionService, $mockLogger);

        $data = [
            'eventLabel' => 'test-label',
            'eventUrl' => 'http://example.com/test',
            'eventTarget' => 'button'
        ];

        $result = $telemetryService->reportClickEvent($data);
        $decodedResult = json_decode($result, true);

        $this->assertEquals(["error" => "Missing required fields"], $decodedResult);
    }

    public function testReportClickEventReturnsErrorForMissingEventLabel(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        $telemetryService = new TelemetryService($mockRepository, $mockVersionService, $mockLogger);

        $data = [
            'eventType' => 'click',
            'eventUrl' => 'http://example.com/test',
            'eventTarget' => 'button'
        ];

        $result = $telemetryService->reportClickEvent($data);
        $decodedResult = json_decode($result, true);

        $this->assertEquals(["error" => "Missing required fields"], $decodedResult);
    }

    public function testReportClickEventReturnsErrorForEmptyEventType(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        $telemetryService = new TelemetryService($mockRepository, $mockVersionService, $mockLogger);

        $data = [
            'eventType' => '',
            'eventLabel' => 'test-label',
            'eventUrl' => 'http://example.com/test',
            'eventTarget' => 'button'
        ];

        $result = $telemetryService->reportClickEvent($data);
        $decodedResult = json_decode($result, true);

        $this->assertEquals(["error" => "Missing required fields"], $decodedResult);
    }

    public function testReportClickEventSuccessfullyReportsEvent(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        $telemetryService = new TelemetryService($mockRepository, $mockVersionService, $mockLogger);

        $data = [
            'eventType' => 'click',
            'eventLabel' => 'test-label',
            'eventUrl' => 'http://example.com/test?param=value',
            'eventTarget' => 'button'
        ];

        // Mock the repository to return success
        $mockRepository->expects($this->once())
            ->method('saveTelemetryEvent')
            ->with(
                $this->callback(function ($eventData) {
                    return $eventData['eventType'] === 'click'
                        && $eventData['eventLabel'] === 'test-label'
                        && $eventData['eventUrl'] === 'http://example.com/test' // Should strip query params
                        && $eventData['eventTarget'] === 'button';
                }),
                $this->isType('string') // currentTime
            )
            ->willReturn(true);

        // Mock the logger to expect debug call
        $mockLogger->expects($this->once())
            ->method('debug')
            ->with(
                'Telemetry Event has been saved',
                [
                    'eventType' => 'click',
                    'eventLabel' => 'test-label',
                    'eventUrl' => 'http://example.com/test',
                    'eventTarget' => 'button'
                ]
            );

        $result = $telemetryService->reportClickEvent($data);
        $decodedResult = json_decode($result, true);

        $this->assertEquals(["success" => true], $decodedResult);
    }

    public function testReportClickEventReturnsErrorWhenRepositoryFails(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        $telemetryService = new TelemetryService($mockRepository, $mockVersionService, $mockLogger);

        $data = [
            'eventType' => 'click',
            'eventLabel' => 'test-label',
            'eventUrl' => 'http://example.com/test',
            'eventTarget' => 'button'
        ];

        // Mock the repository to return failure
        $mockRepository->expects($this->once())
            ->method('saveTelemetryEvent')
            ->willReturn(false);

        // Mock the logger to expect error call
        $mockLogger->expects($this->once())
            ->method('error')
            ->with(
                'Telemetry Event failed to save',
                [
                    'eventType' => 'click',
                    'eventLabel' => 'test-label',
                    'eventUrl' => 'http://example.com/test',
                    'eventTarget' => 'button'
                ]
            );

        $result = $telemetryService->reportClickEvent($data);
        $decodedResult = json_decode($result, true);

        $this->assertEquals(["error" => "Database insertion/update failed"], $decodedResult);
    }

    public function testReportClickEventStripsQueryParameters(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        $telemetryService = new TelemetryService($mockRepository, $mockVersionService, $mockLogger);

        $data = [
            'eventType' => 'click',
            'eventLabel' => 'test-label',
            'eventUrl' => 'http://example.com/test?param1=value1&param2=value2',
            'eventTarget' => 'button'
        ];

        // Verify that query parameters are stripped from the URL
        $mockRepository->expects($this->once())
            ->method('saveTelemetryEvent')
            ->with(
                $this->callback(function ($eventData) {
                    return $eventData['eventUrl'] === 'http://example.com/test';
                }),
                $this->isType('string')
            )
            ->willReturn(true);

        $mockLogger->expects($this->once())->method('debug');

        $telemetryService->reportClickEvent($data);
    }

    public function testReportClickEventWithNormalizeUrlEnabled(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        // Create a partial mock to mock the normalizeUrl method
        $telemetryService = $this->getMockBuilder(TelemetryService::class)
            ->setConstructorArgs([$mockRepository, $mockVersionService, $mockLogger])
            ->onlyMethods(['normalizeUrl'])
            ->getMock();

        $data = [
            'eventType' => 'click',
            'eventLabel' => 'test-label',
            'eventUrl' => 'http://example.com/webroot/interface/test#section?param=value',
            'eventTarget' => 'button'
        ];

        // Mock normalizeUrl method - it should receive the URL with query params stripped
        $telemetryService->expects($this->once())
            ->method('normalizeUrl')
            ->with('http://example.com/webroot/interface/test#section')
            ->willReturn('/interface/test#section');

        // Verify that the normalized URL is used
        $mockRepository->expects($this->once())
            ->method('saveTelemetryEvent')
            ->with(
                $this->callback(function ($eventData) {
                    return $eventData['eventUrl'] === '/interface/test#section';
                }),
                $this->isType('string')
            )
            ->willReturn(true);

        $mockLogger->expects($this->once())->method('debug');

        $result = $telemetryService->reportClickEvent($data, true);
        $decodedResult = json_decode($result, true);

        $this->assertEquals(["success" => true], $decodedResult);
    }

    public function testReportClickEventHandlesMissingOptionalFields(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        $telemetryService = new TelemetryService($mockRepository, $mockVersionService, $mockLogger);

        $data = [
            'eventType' => 'click',
            'eventLabel' => 'test-label'
            // Missing eventUrl and eventTarget
        ];

        // Verify that missing optional fields default to empty strings
        $mockRepository->expects($this->once())
            ->method('saveTelemetryEvent')
            ->with(
                $this->callback(function ($eventData) {
                    return $eventData['eventType'] === 'click'
                        && $eventData['eventLabel'] === 'test-label'
                        && $eventData['eventUrl'] === ''
                        && $eventData['eventTarget'] === '';
                }),
                $this->isType('string')
            )
            ->willReturn(true);

        $mockLogger->expects($this->once())->method('debug');

        $result = $telemetryService->reportClickEvent($data);
        $decodedResult = json_decode($result, true);

        $this->assertEquals(["success" => true], $decodedResult);
    }

    public function testReportUsageDataReturnsFalseWhenTelemetryDisabled(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        // Create a partial mock to mock the isTelemetryEnabled method
        $telemetryService = $this->getMockBuilder(TelemetryService::class)
            ->setConstructorArgs([$mockRepository, $mockVersionService, $mockLogger])
            ->onlyMethods(['isTelemetryEnabled'])
            ->getMock();

        // Mock isTelemetryEnabled to return false (disabled)
        $telemetryService->expects($this->once())
            ->method('isTelemetryEnabled')
            ->willReturn(0);

        $result = $telemetryService->reportUsageData();

        $this->assertFalse($result);
    }

    public function testReportUsageDataReturnsFalseWhenSiteUuidNotFound(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        // Create a partial mock to mock the isTelemetryEnabled method and getUniqueInstallationUuid
        $telemetryService = $this->getMockBuilder(TelemetryService::class)
            ->setConstructorArgs([$mockRepository, $mockVersionService, $mockLogger])
            ->onlyMethods(['isTelemetryEnabled', 'getUniqueInstallationUuid'])
            ->getMock();

        // Mock isTelemetryEnabled to return true (enabled)
        $telemetryService->expects($this->once())
            ->method('isTelemetryEnabled')
            ->willReturn(1);

        // Mock getUniqueInstallationUuid to return empty string
        $telemetryService->expects($this->once())
            ->method('getUniqueInstallationUuid')
            ->willReturn('');

        $result = $telemetryService->reportUsageData();

        $this->assertFalse($result);
    }

    /**
     * Test reportUsageData with successful scenario.
     * Note: This method still has external dependencies (cURL) that would require
     * additional refactoring to fully isolate, but we can test the core logic flow.
     */
    public function testReportUsageDataWithMockedDependencies(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        /** @var GeoTelemetryInterface|MockObject $mockGeoTelemetry */
        $mockGeoTelemetry = $this->createMock(GeoTelemetryInterface::class);

        // Create a partial mock to mock the dependencies we can control
        $telemetryService = $this->getMockBuilder(TelemetryService::class)
            ->setConstructorArgs([$mockRepository, $mockVersionService, $mockLogger])
            ->onlyMethods(['isTelemetryEnabled', 'getUniqueInstallationUuid', 'createGeoTelemetry', 'querySingleRow', 'executeCurlRequest'])
            ->getMock();

        // Mock isTelemetryEnabled to return enabled
        $telemetryService->expects($this->once())
            ->method('isTelemetryEnabled')
            ->willReturn(1);

        // Mock getUniqueInstallationUuid to return a valid UUID
        $telemetryService->expects($this->once())
            ->method('getUniqueInstallationUuid')
            ->willReturn('test-uuid-123');

        // Mock createGeoTelemetry to return our mock
        $telemetryService->expects($this->once())
            ->method('createGeoTelemetry')
            ->willReturn($mockGeoTelemetry);

        // Mock GeoTelemetry to return geo data
        $mockGeoTelemetry->expects($this->once())
            ->method('getServerGeoData')
            ->willReturn(['country' => 'US', 'region' => 'CA']);

        // Mock database query for timezone
        $telemetryService->expects($this->once())
            ->method('querySingleRow')
            ->with(
                "SELECT `gl_value` as zone FROM `globals` WHERE `gl_value` > '' AND `gl_name` = 'gbl_time_zone' LIMIT 1",
                []
            )
            ->willReturn(['zone' => 'America/New_York']);

        // Mock repository methods
        $mockRepository->expects($this->once())
            ->method('fetchUsageRecords')
            ->willReturn([
                ['event_type' => 'click', 'event_label' => 'test', 'count' => 5]
            ]);

        // Mock version service
        $mockVersionService->expects($this->once())
            ->method('asString')
            ->willReturn('7.0.0');

        // Mock successful HTTP response
        $telemetryService->expects($this->once())
            ->method('executeCurlRequest')
            ->with(
                $this->stringContains('https://reg.open-emr.org/api/usage?SiteID=test-uuid-123'),
                $this->isType('string')
            )
            ->willReturn([
                'response' => '{"status": "success"}',
                'httpStatus' => 200,
                'error' => null
            ]);

        // Expect clearTelemetryData to be called on successful response
        $mockRepository->expects($this->once())
            ->method('clearTelemetryData');

        $result = $telemetryService->reportUsageData();

        $this->assertEquals(200, $result);
    }

    public function testReportUsageDataHandlesGeoTelemetryError(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        /** @var GeoTelemetryInterface|MockObject $mockGeoTelemetry */
        $mockGeoTelemetry = $this->createMock(GeoTelemetryInterface::class);

        // Create a partial mock
        $telemetryService = $this->getMockBuilder(TelemetryService::class)
            ->setConstructorArgs([$mockRepository, $mockVersionService, $mockLogger])
            ->onlyMethods(['isTelemetryEnabled', 'getUniqueInstallationUuid', 'createGeoTelemetry', 'querySingleRow', 'executeCurlRequest'])
            ->getMock();

        // Setup basic mocks
        $telemetryService->expects($this->once())
            ->method('isTelemetryEnabled')
            ->willReturn(1);

        $telemetryService->expects($this->once())
            ->method('getUniqueInstallationUuid')
            ->willReturn('test-uuid-123');

        $telemetryService->expects($this->once())
            ->method('createGeoTelemetry')
            ->willReturn($mockGeoTelemetry);

        // Mock geo telemetry error
        $mockGeoTelemetry->expects($this->once())
            ->method('getServerGeoData')
            ->willReturn(['error' => 'Failed to fetch geo data']);

        $telemetryService->expects($this->once())
            ->method('querySingleRow')
            ->willReturn(['zone' => 'UTC']);

        $mockRepository->expects($this->once())
            ->method('fetchUsageRecords')
            ->willReturn([]);

        $mockVersionService->expects($this->once())
            ->method('asString')
            ->willReturn('7.0.0');

        // Mock successful HTTP response despite geo error
        $telemetryService->expects($this->once())
            ->method('executeCurlRequest')
            ->willReturn([
                'response' => '{"status": "success"}',
                'httpStatus' => 200,
                'error' => null
            ]);

        // Should still call clearTelemetryData on successful HTTP response
        $mockRepository->expects($this->once())
            ->method('clearTelemetryData');

        $result = $telemetryService->reportUsageData();

        // Verify that the method continues execution despite geo telemetry error
        $this->assertEquals(200, $result);
    }

    public function testReportUsageDataHandlesHttpError(): void
    {
        /** @var TelemetryRepository|MockObject $mockRepository */
        $mockRepository = $this->createMock(TelemetryRepository::class);

        /** @var VersionServiceInterface|MockObject $mockVersionService */
        $mockVersionService = $this->createMock(VersionServiceInterface::class);

        /** @var SystemLogger|MockObject $mockLogger */
        $mockLogger = $this->createMock(SystemLogger::class);

        /** @var GeoTelemetryInterface|MockObject $mockGeoTelemetry */
        $mockGeoTelemetry = $this->createMock(GeoTelemetryInterface::class);

        // Create a partial mock
        $telemetryService = $this->getMockBuilder(TelemetryService::class)
            ->setConstructorArgs([$mockRepository, $mockVersionService, $mockLogger])
            ->onlyMethods(['isTelemetryEnabled', 'getUniqueInstallationUuid', 'createGeoTelemetry', 'querySingleRow', 'executeCurlRequest'])
            ->getMock();

        // Setup basic mocks
        $telemetryService->expects($this->once())
            ->method('isTelemetryEnabled')
            ->willReturn(1);

        $telemetryService->expects($this->once())
            ->method('getUniqueInstallationUuid')
            ->willReturn('test-uuid-123');

        $telemetryService->expects($this->once())
            ->method('createGeoTelemetry')
            ->willReturn($mockGeoTelemetry);

        $mockGeoTelemetry->expects($this->once())
            ->method('getServerGeoData')
            ->willReturn(['country' => 'US']);

        $telemetryService->expects($this->once())
            ->method('querySingleRow')
            ->willReturn(['zone' => 'America/New_York']);

        $mockRepository->expects($this->once())
            ->method('fetchUsageRecords')
            ->willReturn([]);

        $mockVersionService->expects($this->once())
            ->method('asString')
            ->willReturn('7.0.0');

        // Mock HTTP error response
        $telemetryService->expects($this->once())
            ->method('executeCurlRequest')
            ->willReturn([
                'response' => '',
                'httpStatus' => 500,
                'error' => null
            ]);

        // Should NOT call clearTelemetryData on HTTP error
        $mockRepository->expects($this->never())
            ->method('clearTelemetryData');

        $result = $telemetryService->reportUsageData();

        $this->assertEquals(500, $result);
    }
}
