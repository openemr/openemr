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
use OpenEMR\Services\VersionServiceInterface;
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
}
