<?php

/**
 * Isolated GeoTelemetry Test
 *
 * Tests GeoTelemetry functionality without external network dependencies.
 * Uses PHPUnit mocks to test business logic in isolation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Telemetry;

use OpenEMR\Telemetry\GeoTelemetry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GeoTelemetryTest extends TestCase
{
    public function testAnonymizeIpReturnsHashedValue(): void
    {
        $geoTelemetry = new GeoTelemetry();

        $ip = '192.168.1.1';
        $result = $geoTelemetry->anonymizeIp($ip);

        // Should return a SHA-256 hash
        $this->assertIsString($result);
        $this->assertEquals(64, strlen($result)); // SHA-256 produces 64 character hex string
        $this->assertEquals(hash('sha256', $ip), $result);
    }

    public function testAnonymizeIpReturnsSameHashForSameIp(): void
    {
        $geoTelemetry = new GeoTelemetry();

        $ip = '10.0.0.1';
        $result1 = $geoTelemetry->anonymizeIp($ip);
        $result2 = $geoTelemetry->anonymizeIp($ip);

        $this->assertEquals($result1, $result2);
    }

    public function testGetGeoDataWithIpapiSuccess(): void
    {
        /** @var GeoTelemetry|MockObject $geoTelemetry */
        $geoTelemetry = $this->getMockBuilder(GeoTelemetry::class)
            ->onlyMethods(['fileGetContents'])
            ->getMock();

        $mockIpapiResponse = json_encode([
            'country_name' => 'United States',
            'region' => 'California',
            'city' => 'San Francisco',
            'latitude' => 37.7749,
            'longitude' => -122.4194
        ]);

        // Mock fileGetContents to return ipapi.co response on first call
        $geoTelemetry->expects($this->once())
            ->method('fileGetContents')
            ->with('https://ipapi.co/8.8.8.8/json/')
            ->willReturn($mockIpapiResponse);

        $result = $geoTelemetry->getGeoData('8.8.8.8');

        $expected = [
            'country' => 'United States',
            'region' => 'California',
            'city' => 'San Francisco',
            'latitude' => 37.7749,
            'longitude' => -122.4194
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetGeoDataWithFallbackToGeoplugin(): void
    {
        /** @var GeoTelemetry|MockObject $geoTelemetry */
        $geoTelemetry = $this->getMockBuilder(GeoTelemetry::class)
            ->onlyMethods(['fileGetContents'])
            ->getMock();

        $mockGeopluginResponse = json_encode([
            'geoplugin_countryName' => 'Canada',
            'geoplugin_region' => 'Ontario',
            'geoplugin_city' => 'Toronto',
            'geoplugin_latitude' => 43.6532,
            'geoplugin_longitude' => -79.3832
        ]);

        // First call (ipapi.co) returns empty, second call (geoplugin) returns data
        $geoTelemetry->expects($this->exactly(2))
            ->method('fileGetContents')
            ->willReturnOnConsecutiveCalls(
                '', // ipapi.co fails (empty response)
                $mockGeopluginResponse // geoplugin succeeds
            );

        $result = $geoTelemetry->getGeoData('1.1.1.1');

        $expected = [
            'country' => 'Canada',
            'region' => 'Ontario',
            'city' => 'Toronto',
            'latitude' => 43.6532,
            'longitude' => -79.3832
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetGeoDataReturnsErrorWhenBothApiFail(): void
    {
        /** @var GeoTelemetry|MockObject $geoTelemetry */
        $geoTelemetry = $this->getMockBuilder(GeoTelemetry::class)
            ->onlyMethods(['fileGetContents'])
            ->getMock();

        // Both API calls return empty strings
        $geoTelemetry->expects($this->exactly(2))
            ->method('fileGetContents')
            ->willReturn('');

        $result = $geoTelemetry->getGeoData('192.168.1.1');

        $this->assertEquals(['error' => 'IP lookup failed'], $result);
    }

    public function testGetGeoDataHandlesPartialData(): void
    {
        /** @var GeoTelemetry|MockObject $geoTelemetry */
        $geoTelemetry = $this->getMockBuilder(GeoTelemetry::class)
            ->onlyMethods(['fileGetContents'])
            ->getMock();

        $mockPartialResponse = json_encode([
            'country_name' => 'Germany',
            'region' => null,
            'city' => 'Berlin'
            // Missing latitude and longitude
        ]);

        $geoTelemetry->expects($this->once())
            ->method('fileGetContents')
            ->willReturn($mockPartialResponse);

        $result = $geoTelemetry->getGeoData('1.2.3.4');

        $expected = [
            'country' => 'Germany',
            'region' => null,
            'city' => 'Berlin',
            'latitude' => null,
            'longitude' => null
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetServerGeoDataSuccess(): void
    {
        /** @var GeoTelemetry|MockObject $geoTelemetry */
        $geoTelemetry = $this->getMockBuilder(GeoTelemetry::class)
            ->onlyMethods(['fileGetContents'])
            ->getMock();

        $mockIp = '203.0.113.1';
        $mockGeoDataResponse = json_encode([
            'country_name' => 'Australia',
            'region' => 'New South Wales',
            'city' => 'Sydney',
            'latitude' => -33.8688,
            'longitude' => 151.2093
        ]);

        // First call gets the server IP, second call gets geo data for that IP
        $geoTelemetry->expects($this->exactly(2))
            ->method('fileGetContents')
            ->willReturnOnConsecutiveCalls(
                $mockIp, // ipify returns the IP
                $mockGeoDataResponse // ipapi returns geo data
            );

        $result = $geoTelemetry->getServerGeoData();

        $expected = [
            'country' => 'Australia',
            'region' => 'New South Wales',
            'city' => 'Sydney',
            'latitude' => -33.8688,
            'longitude' => 151.2093
        ];

        $this->assertEquals($expected, $result);
    }

    public function testGetServerGeoDataWithInvalidIp(): void
    {
        /** @var GeoTelemetry|MockObject $geoTelemetry */
        $geoTelemetry = $this->getMockBuilder(GeoTelemetry::class)
            ->onlyMethods(['fileGetContents'])
            ->getMock();

        // Mock fileGetContents to return invalid IP
        $geoTelemetry->expects($this->once())
            ->method('fileGetContents')
            ->with('https://api.ipify.org', false, $this->anything())
            ->willReturn('not-an-ip-address');

        $result = $geoTelemetry->getServerGeoData();

        $this->assertEquals(['error' => 'Unable to determine server IP'], $result);
    }

    public function testGetServerGeoDataWithEmptyResponse(): void
    {
        /** @var GeoTelemetry|MockObject $geoTelemetry */
        $geoTelemetry = $this->getMockBuilder(GeoTelemetry::class)
            ->onlyMethods(['fileGetContents'])
            ->getMock();

        // Mock fileGetContents to return empty string
        $geoTelemetry->expects($this->once())
            ->method('fileGetContents')
            ->with('https://api.ipify.org', false, $this->anything())
            ->willReturn('');

        $result = $geoTelemetry->getServerGeoData();

        $this->assertEquals(['error' => 'Unable to determine server IP'], $result);
    }

    public function testGetServerGeoDataTrimsWhitespace(): void
    {
        /** @var GeoTelemetry|MockObject $geoTelemetry */
        $geoTelemetry = $this->getMockBuilder(GeoTelemetry::class)
            ->onlyMethods(['fileGetContents'])
            ->getMock();

        $mockIpWithWhitespace = "  203.0.113.1\n  ";
        $mockGeoDataResponse = json_encode([
            'country_name' => 'Test Country',
            'region' => 'Test Region',
            'city' => 'Test City',
            'latitude' => 40.7128,
            'longitude' => -74.0060
        ]);

        // Mock fileGetContents to return IP with whitespace, then geo data
        $geoTelemetry->expects($this->exactly(2))
            ->method('fileGetContents')
            ->willReturnOnConsecutiveCalls(
                $mockIpWithWhitespace, // ipify returns IP with whitespace
                $mockGeoDataResponse // ipapi returns geo data for trimmed IP
            );

        $result = $geoTelemetry->getServerGeoData();

        $expected = [
            'country' => 'Test Country',
            'region' => 'Test Region',
            'city' => 'Test City',
            'latitude' => 40.7128,
            'longitude' => -74.0060
        ];

        $this->assertEquals($expected, $result);
    }

    public function testMethodSignatures(): void
    {
        $geoTelemetry = new GeoTelemetry();

        // Test anonymizeIp method signature
        $this->assertTrue(method_exists($geoTelemetry, 'anonymizeIp'));
        $reflection = new \ReflectionMethod($geoTelemetry, 'anonymizeIp');
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        // Test getGeoData method signature
        $this->assertTrue(method_exists($geoTelemetry, 'getGeoData'));
        $reflection = new \ReflectionMethod($geoTelemetry, 'getGeoData');
        $this->assertEquals(1, $reflection->getNumberOfParameters());
        $this->assertEquals('array', $reflection->getReturnType()->getName());

        // Test getServerGeoData method signature
        $this->assertTrue(method_exists($geoTelemetry, 'getServerGeoData'));
        $reflection = new \ReflectionMethod($geoTelemetry, 'getServerGeoData');
        $this->assertEquals(0, $reflection->getNumberOfParameters());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }
}
