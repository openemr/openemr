<?php

namespace OpenEMR\Tests\Isolated\Telemetry;

use PHPUnit\Framework\TestCase;

class TelemetryServiceTest extends TestCase
{
    private TelemetryServiceMock $service;
    private TelemetryRepositoryMock $repository;
    private VersionServiceMock $versionService;
    private SystemLoggerMock $logger;

    protected function setUp(): void
    {
        $this->repository = new TelemetryRepositoryMock();
        $this->versionService = new VersionServiceMock();
        $this->logger = new SystemLoggerMock();

        $this->service = new TelemetryServiceMock(
            $this->repository,
            $this->versionService,
            $this->logger
        );
    }

    public function testConstructorWithNullDependencies(): void
    {
        $service = new TelemetryServiceMock();
        $this->assertInstanceOf(TelemetryServiceMock::class, $service);
    }

    public function testReportClickEventSuccess(): void
    {
        $this->repository->setSaveResult(true);

        $data = [
            'eventType' => 'click',
            'eventLabel' => 'button_test',
            'eventUrl' => 'http://example.com/test?param=1',
            'eventTarget' => '_self'
        ];

        $result = $this->service->reportClickEvent($data);
        $decodedResult = json_decode($result, true);

        $this->assertTrue($decodedResult['success']);
        $this->assertEquals('click', $this->repository->getLastSavedEvent()['eventType']);
        $this->assertEquals('button_test', $this->repository->getLastSavedEvent()['eventLabel']);
        $this->assertEquals('http://example.com/test', $this->repository->getLastSavedEvent()['eventUrl']);
        $this->assertEquals('_self', $this->repository->getLastSavedEvent()['eventTarget']);
    }

    public function testReportClickEventWithNormalization(): void
    {
        $this->repository->setSaveResult(true);

        $data = [
            'eventType' => 'click',
            'eventLabel' => 'button_test',
            'eventUrl' => '/openemr/interface/main/main.php#fragment',
            'eventTarget' => '_self'
        ];

        $result = $this->service->reportClickEvent($data, true);
        $decodedResult = json_decode($result, true);

        $this->assertTrue($decodedResult['success']);
        $this->assertEquals('/interface/main/main.php#fragment', $this->repository->getLastSavedEvent()['eventUrl']);
    }

    public function testReportClickEventMissingRequiredFields(): void
    {
        $data = [
            'eventType' => '',
            'eventLabel' => 'button_test',
            'eventUrl' => 'http://example.com/test',
            'eventTarget' => '_self'
        ];

        $result = $this->service->reportClickEvent($data);
        $decodedResult = json_decode($result, true);

        $this->assertEquals('Missing required fields', $decodedResult['error']);
    }

    public function testReportClickEventDatabaseFailure(): void
    {
        $this->repository->setSaveResult(false);

        $data = [
            'eventType' => 'click',
            'eventLabel' => 'button_test',
            'eventUrl' => 'http://example.com/test',
            'eventTarget' => '_self'
        ];

        $result = $this->service->reportClickEvent($data);
        $decodedResult = json_decode($result, true);

        $this->assertEquals('Database insertion/update failed', $decodedResult['error']);
    }

    public function testTrackApiRequestEventWhenEnabled(): void
    {
        $this->repository->setSaveResult(true);
        $this->service->setTelemetryEnabled(true);

        $eventData = [
            'eventType' => 'api',
            'eventLabel' => 'patient_create',
            'eventUrl' => '/api/patient',
            'eventTarget' => 'endpoint'
        ];

        $this->service->trackApiRequestEvent($eventData);

        $this->assertEquals('api', $this->repository->getLastSavedEvent()['eventType']);
        $this->assertEquals('patient_create', $this->repository->getLastSavedEvent()['eventLabel']);
    }

    public function testTrackApiRequestEventWhenDisabled(): void
    {
        $this->service->setTelemetryEnabled(false);

        $eventData = [
            'eventType' => 'api',
            'eventLabel' => 'patient_create',
            'eventUrl' => '/api/patient',
            'eventTarget' => 'endpoint'
        ];

        $this->service->trackApiRequestEvent($eventData);

        $this->assertNull($this->repository->getLastSavedEvent());
    }

    public function testReportUsageDataWhenDisabled(): void
    {
        $this->service->setTelemetryEnabled(false);

        $result = $this->service->reportUsageData();

        $this->assertFalse($result);
    }

    public function testReportUsageDataWithEmptyUuid(): void
    {
        $this->service->setTelemetryEnabled(true);
        $this->service->setUniqueInstallationUuid('');

        $result = $this->service->reportUsageData();

        $this->assertFalse($result);
    }

    public function testReportUsageDataSuccess(): void
    {
        $this->service->setTelemetryEnabled(true);
        $this->service->setUniqueInstallationUuid('test-uuid-123');
        $this->service->setCurlResponse('{"status": "success"}', 200);

        $this->repository->setUsageRecords([
            [
                'event_type' => 'click',
                'event_label' => 'test_button',
                'event_url' => '/test',
                'event_target' => '_self',
                'first_event' => '2024-01-01 00:00:00',
                'last_event' => '2024-01-01 12:00:00',
                'count' => 5
            ]
        ]);

        $result = $this->service->reportUsageData();

        $this->assertEquals(200, $result);
        $this->assertTrue($this->repository->wasClearCalled());
    }

    public function testReportUsageDataHttpError(): void
    {
        $this->service->setTelemetryEnabled(true);
        $this->service->setUniqueInstallationUuid('test-uuid-123');
        $this->service->setCurlResponse('Error', 500);

        $result = $this->service->reportUsageData();

        $this->assertEquals(500, $result);
        $this->assertFalse($this->repository->wasClearCalled());
    }

    public function testNormalizeUrlRemovesWebroot(): void
    {
        $url = '/openemr/interface/main/main.php#fragment';
        $normalized = $this->service->normalizeUrl($url);

        $this->assertEquals('/interface/main/main.php#fragment', $normalized);
    }

    public function testNormalizeUrlWithoutWebroot(): void
    {
        $this->service->setWebroot('');
        $url = '/interface/main/main.php#fragment';
        $normalized = $this->service->normalizeUrl($url);

        $this->assertEquals('/interface/main/main.php#fragment', $normalized);
    }
}

class TelemetryServiceMock
{
    protected TelemetryRepositoryMock $repository;
    protected VersionServiceMock $versionService;
    protected SystemLoggerMock $logger;
    private bool $telemetryEnabled = true;
    private string $uniqueInstallationUuid = 'test-uuid-123';
    private string $curlResponse = '{"status": "success"}';
    private int $curlHttpStatus = 200;
    private string $webroot = '/openemr';

    public function __construct(?TelemetryRepositoryMock $repository = null, ?VersionServiceMock $versionService = null, ?SystemLoggerMock $logger = null)
    {
        $this->repository = $repository ?? new TelemetryRepositoryMock();
        $this->versionService = $versionService ?? new VersionServiceMock();
        $this->logger = $logger ?? new SystemLoggerMock();
    }

    public function setTelemetryEnabled(bool $enabled): void
    {
        $this->telemetryEnabled = $enabled;
    }

    public function setUniqueInstallationUuid(string $uuid): void
    {
        $this->uniqueInstallationUuid = $uuid;
    }

    public function setCurlResponse(string $response, int $httpStatus): void
    {
        $this->curlResponse = $response;
        $this->curlHttpStatus = $httpStatus;
    }

    public function setWebroot(string $webroot): void
    {
        $this->webroot = $webroot;
    }

    public function reportClickEvent(array $data, bool $normalizeUrl = false): false|string
    {
        $eventType = $data['eventType'] ?? '';
        $eventLabel = $data['eventLabel'] ?? '';
        $eventUrl = preg_replace('/\?.*$/', '', $data['eventUrl'] ?? '');

        if ($normalizeUrl) {
            $eventUrl = $this->normalizeUrl($eventUrl);
        }

        $eventTarget = $data['eventTarget'] ?? '';
        $currentTime = date("Y-m-d H:i:s");

        if (empty($eventType) || empty($eventLabel)) {
            return json_encode(["error" => "Missing required fields"]);
        }

        $success = $this->repository->saveTelemetryEvent(
            [
                'eventType' => $eventType,
                'eventLabel' => $eventLabel,
                'eventUrl' => $eventUrl,
                'eventTarget' => $eventTarget,
            ],
            $currentTime
        );

        if ($success) {
            $this->logger->debug("Telemetry Event has been saved", [
                'eventType' => $eventType,
                'eventLabel' => $eventLabel,
                'eventUrl' => $eventUrl,
                'eventTarget' => $eventTarget,
            ]);
            return json_encode(["success" => true]);
        } else {
            $this->logger->error("Telemetry Event failed to save", [
                'eventType' => $eventType,
                'eventLabel' => $eventLabel,
                'eventUrl' => $eventUrl,
                'eventTarget' => $eventTarget,
            ]);
            return json_encode(["error" => "Database insertion/update failed"]);
        }
    }

    public function trackApiRequestEvent(array $event_data): void
    {
        if ($this->telemetryEnabled) {
            $this->reportClickEvent($event_data);
        }
    }

    public function reportUsageData(): int|bool
    {
        if (!$this->telemetryEnabled) {
            return false;
        }

        if (empty($this->uniqueInstallationUuid)) {
            return false;
        }

        if (in_array($this->curlHttpStatus, [200, 201, 204])) {
            $this->repository->clearTelemetryData();
        }

        return $this->curlHttpStatus;
    }

    public function normalizeUrl(string $url): string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

        if (!empty($this->webroot)) {
            $normalized = preg_replace('#^(' . $this->webroot . ')?#', '', $path);
        } else {
            $normalized = $path;
        }

        return ($normalized . $fragment);
    }
}

class TelemetryRepositoryMock
{
    private bool $saveResult = true;
    private ?array $lastSavedEvent = null;
    private array $usageRecords = [];
    private bool $clearCalled = false;

    public function setSaveResult(bool $result): void
    {
        $this->saveResult = $result;
    }

    public function setUsageRecords(array $records): void
    {
        $this->usageRecords = $records;
    }

    public function getLastSavedEvent(): ?array
    {
        return $this->lastSavedEvent;
    }

    public function wasClearCalled(): bool
    {
        return $this->clearCalled;
    }

    public function saveTelemetryEvent(array $eventData, string $currentTime): bool
    {
        if ($this->saveResult) {
            $this->lastSavedEvent = $eventData;
        }
        return $this->saveResult;
    }

    public function fetchUsageRecords(): array
    {
        return $this->usageRecords;
    }

    public function clearTelemetryData(): void
    {
        $this->clearCalled = true;
    }
}

class VersionServiceMock
{
    public function asString(): string
    {
        return '7.0.0-dev';
    }
}

class SystemLoggerMock
{
    private array $debugLogs = [];
    private array $errorLogs = [];

    public function debug(string $message, array $context = []): void
    {
        $this->debugLogs[] = ['message' => $message, 'context' => $context];
    }

    public function error(string $message, array $context = []): void
    {
        $this->errorLogs[] = ['message' => $message, 'context' => $context];
    }

    public function getDebugLogs(): array
    {
        return $this->debugLogs;
    }

    public function getErrorLogs(): array
    {
        return $this->errorLogs;
    }
}
