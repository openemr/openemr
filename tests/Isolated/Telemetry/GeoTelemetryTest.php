<?php

namespace OpenEMR\Tests\Isolated\Telemetry;

use PHPUnit\Framework\TestCase;

class GeoTelemetryTest extends TestCase
{
    private GeoTelemetryIsolated $geoTelemetry;

    protected function setUp(): void
    {
        $this->geoTelemetry = new GeoTelemetryIsolated();
    }

    public function testAnonymizeIp(): void
    {
        $ip = '192.168.1.1';
        $anonymized = $this->geoTelemetry->anonymizeIp($ip);

        $this->assertIsString($anonymized);
        $this->assertEquals(64, strlen($anonymized));
        $this->assertEquals(hash('sha256', $ip), $anonymized);
    }

    public function testAnonymizeIpDifferentInputs(): void
    {
        $ip1 = '192.168.1.1';
        $ip2 = '10.0.0.1';

        $anonymized1 = $this->geoTelemetry->anonymizeIp($ip1);
        $anonymized2 = $this->geoTelemetry->anonymizeIp($ip2);

        $this->assertNotEquals($anonymized1, $anonymized2);
    }

    public function testAnonymizeIpConsistentHashing(): void
    {
        $ip = '203.0.113.1';

        $anonymized1 = $this->geoTelemetry->anonymizeIp($ip);
        $anonymized2 = $this->geoTelemetry->anonymizeIp($ip);

        $this->assertEquals($anonymized1, $anonymized2);
    }

    public function testGetGeoDataSuccessfulIpapi(): void
    {
        $mockData = [
            'country_name' => 'United States',
            'region' => 'California',
            'city' => 'San Francisco',
            'latitude' => 37.7749,
            'longitude' => -122.4194
        ];

        $this->geoTelemetry->setMockApiResponse('ipapi', $mockData);

        $result = $this->geoTelemetry->getGeoData('8.8.8.8');

        $this->assertEquals('United States', $result['country']);
        $this->assertEquals('California', $result['region']);
        $this->assertEquals('San Francisco', $result['city']);
        $this->assertEquals(37.7749, $result['latitude']);
        $this->assertEquals(-122.4194, $result['longitude']);
    }

    public function testGetGeoDataFallbackToGeoplugin(): void
    {
        $this->geoTelemetry->setMockApiResponse('ipapi', []);

        $mockGeopluginData = [
            'geoplugin_countryName' => 'Canada',
            'geoplugin_region' => 'Ontario',
            'geoplugin_city' => 'Toronto',
            'geoplugin_latitude' => '43.6532',
            'geoplugin_longitude' => '-79.3832'
        ];

        $this->geoTelemetry->setMockApiResponse('geoplugin', $mockGeopluginData);

        $result = $this->geoTelemetry->getGeoData('8.8.8.8');

        $this->assertEquals('Canada', $result['country']);
        $this->assertEquals('Ontario', $result['region']);
        $this->assertEquals('Toronto', $result['city']);
        $this->assertEquals('43.6532', $result['latitude']);
        $this->assertEquals('-79.3832', $result['longitude']);
    }

    public function testGetGeoDataBothApisFail(): void
    {
        $this->geoTelemetry->setMockApiResponse('ipapi', []);
        $this->geoTelemetry->setMockApiResponse('geoplugin', []);

        $result = $this->geoTelemetry->getGeoData('8.8.8.8');

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('IP lookup failed', $result['error']);
    }

    public function testGetGeoDataWithNullValues(): void
    {
        $mockData = [
            'country_name' => 'United States',
            'region' => null,
            'city' => null,
            'latitude' => 37.7749,
            'longitude' => null
        ];

        $this->geoTelemetry->setMockApiResponse('ipapi', $mockData);

        $result = $this->geoTelemetry->getGeoData('8.8.8.8');

        $this->assertEquals('United States', $result['country']);
        $this->assertNull($result['region']);
        $this->assertNull($result['city']);
        $this->assertEquals(37.7749, $result['latitude']);
        $this->assertNull($result['longitude']);
    }

    public function testGetGeoDataWithPartialIpapiData(): void
    {
        $mockIpapiData = [
            'country_name' => 'United States'
        ];

        $this->geoTelemetry->setMockApiResponse('ipapi', $mockIpapiData);

        $result = $this->geoTelemetry->getGeoData('8.8.8.8');

        $this->assertEquals('United States', $result['country']);
        $this->assertNull($result['region']);
        $this->assertNull($result['city']);
        $this->assertNull($result['latitude']);
        $this->assertNull($result['longitude']);
    }

    public function testGetServerGeoDataSuccess(): void
    {
        $this->geoTelemetry->setMockServerIp('203.0.113.1');

        $mockGeoData = [
            'country_name' => 'United Kingdom',
            'region' => 'England',
            'city' => 'London',
            'latitude' => 51.5074,
            'longitude' => -0.1278
        ];

        $this->geoTelemetry->setMockApiResponse('ipapi', $mockGeoData);

        $result = $this->geoTelemetry->getServerGeoData();

        $this->assertEquals('United Kingdom', $result['country']);
        $this->assertEquals('England', $result['region']);
        $this->assertEquals('London', $result['city']);
        $this->assertEquals(51.5074, $result['latitude']);
        $this->assertEquals(-0.1278, $result['longitude']);
    }

    public function testGetServerGeoDataInvalidIp(): void
    {
        $this->geoTelemetry->setMockServerIp('invalid-ip');

        $result = $this->geoTelemetry->getServerGeoData();

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Unable to determine server IP', $result['error']);
    }

    public function testGetServerGeoDataEmptyIp(): void
    {
        $this->geoTelemetry->setMockServerIp('');

        $result = $this->geoTelemetry->getServerGeoData();

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('Unable to determine server IP', $result['error']);
    }

    public function testGetServerGeoDataIpLookupFails(): void
    {
        $this->geoTelemetry->setMockServerIp('8.8.8.8');
        $this->geoTelemetry->setMockApiResponse('ipapi', []);
        $this->geoTelemetry->setMockApiResponse('geoplugin', []);

        $result = $this->geoTelemetry->getServerGeoData();

        $this->assertArrayHasKey('error', $result);
        $this->assertEquals('IP lookup failed', $result['error']);
    }

    public function testGetGeoDataPrivateIpAddress(): void
    {
        $mockData = [
            'country_name' => 'Reserved',
            'region' => 'Private Network',
            'city' => 'Local',
            'latitude' => 0,
            'longitude' => 0
        ];

        $this->geoTelemetry->setMockApiResponse('ipapi', $mockData);

        $result = $this->geoTelemetry->getGeoData('192.168.1.1');

        $this->assertEquals('Reserved', $result['country']);
        $this->assertEquals('Private Network', $result['region']);
        $this->assertEquals('Local', $result['city']);
    }

    public function testGetGeoDataLoopbackAddress(): void
    {
        $mockData = [
            'country_name' => 'Reserved',
            'region' => 'Loopback',
            'city' => 'localhost',
            'latitude' => 0,
            'longitude' => 0
        ];

        $this->geoTelemetry->setMockApiResponse('ipapi', $mockData);

        $result = $this->geoTelemetry->getGeoData('127.0.0.1');

        $this->assertEquals('Reserved', $result['country']);
        $this->assertEquals('Loopback', $result['region']);
        $this->assertEquals('localhost', $result['city']);
    }
}

class GeoTelemetryIsolated
{
    private array $mockApiResponses = [];
    private string $mockServerIp = '8.8.8.8';

    public function setMockApiResponse(string $api, array $data): void
    {
        $this->mockApiResponses[$api] = $data;
    }

    public function setMockServerIp(string $ip): void
    {
        $this->mockServerIp = $ip;
    }

    public function anonymizeIp(string $ip): string
    {
        return hash('sha256', $ip);
    }

    public function getGeoData(string $ip): array
    {
        $data = $this->fetch("https://ipapi.co/{$ip}/json/");
        if (isset($data['country_name'])) {
            return [
                'country' => $data['country_name'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
            ];
        }

        $data = $this->fetch("http://www.geoplugin.net/json.gp?ip={$ip}");
        if (isset($data['geoplugin_countryName'])) {
            return [
                'country' => $data['geoplugin_countryName'] ?? null,
                'region' => $data['geoplugin_region'] ?? null,
                'city' => $data['geoplugin_city'] ?? null,
                'latitude' => $data['geoplugin_latitude'] ?? null,
                'longitude' => $data['geoplugin_longitude'] ?? null,
            ];
        }

        return ['error' => 'IP lookup failed'];
    }

    public function getServerGeoData(): array
    {
        $ip = trim($this->mockServerIp);
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return $this->getGeoData($ip);
        }
        return ['error' => 'Unable to determine server IP'];
    }

    private function fetch(string $url): array
    {
        if (strpos($url, 'ipapi.co') !== false) {
            return $this->mockApiResponses['ipapi'] ?? [];
        }

        if (strpos($url, 'geoplugin.net') !== false) {
            return $this->mockApiResponses['geoplugin'] ?? [];
        }

        return [];
    }
}
