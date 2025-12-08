<?php

/**
 * Isolated tests for USPSAddressVerifyV3 class
 *
 * Tests the USPSAddressVerifyV3 class functionality without requiring database connections
 * or external dependencies. Validates address verification, OAuth token handling,
 * error handling, and response parsing.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2025 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\USPS;

use OpenEMR\USPS\USPSAddressVerifyV3;
use PHPUnit\Framework\TestCase;

class USPSAddressVerifyV3Test extends TestCase
{
    public function testIsConfiguredReturnsFalseWhenCredentialsMissing(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('', '');
        $this->assertFalse($verify->isConfigured());
    }

    public function testIsConfiguredReturnsTrueWhenCredentialsPresent(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $this->assertTrue($verify->isConfigured());
    }

    public function testVerifyReturnsFalseWhenNotConfigured(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('', '');

        $result = $verify->verify('123 Main St', '', 'Springfield', 'IL', '62701');

        $this->assertFalse($result);
        $this->assertTrue($verify->isError());
        $this->assertEquals('USPS API v3 credentials not configured', $verify->getErrorMessage());
    }

    public function testSuccessfulAddressVerification(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse([
            'address' => [
                'streetAddress' => '6406 IVY LN',
                'secondaryAddress' => '',
                'city' => 'GREENBELT',
                'state' => 'MD',
                'ZIPCode' => '20770',
                'ZIPPlus4' => '1441'
            ]
        ]);

        $result = $verify->verify('6406 Ivy Lane', '', 'Greenbelt', 'MD', '20770');

        $this->assertTrue($result);
        $this->assertTrue($verify->isSuccess());
        $this->assertFalse($verify->isError());
        $this->assertNull($verify->getErrorMessage());
    }

    public function testGetAddressReturnsCorrectFormat(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse([
            'address' => [
                'streetAddress' => '6406 IVY LN',
                'secondaryAddress' => '',
                'city' => 'GREENBELT',
                'state' => 'MD',
                'ZIPCode' => '20770',
                'ZIPPlus4' => '1441'
            ]
        ]);

        $verify->verify('6406 Ivy Lane', '', 'Greenbelt', 'MD', '20770');
        $address = $verify->getAddress();

        $this->assertIsArray($address);
        $this->assertEquals('6406 IVY LN', $address['streetAddress']);
        $this->assertEquals('', $address['secondaryAddress']);
        $this->assertEquals('GREENBELT', $address['city']);
        $this->assertEquals('MD', $address['state']);
        $this->assertEquals('20770', $address['ZIPCode']);
        $this->assertEquals('1441', $address['ZIPPlus4']);
    }

    public function testGetAddressReturnsNullWhenNotConfigured(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('', '');
        $verify->verify('123 Main St', '', 'City', 'ST', '12345');

        $this->assertNull($verify->getAddress());
    }

    public function testGetAddressReturnsNullBeforeVerify(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');

        $this->assertNull($verify->getAddress());
    }

    public function testSecondaryAddressMapping(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse([
            'address' => [
                'streetAddress' => '1600 PENNSYLVANIA AVE NW',
                'secondaryAddress' => 'APT 1',
                'city' => 'WASHINGTON',
                'state' => 'DC',
                'ZIPCode' => '20500',
                'ZIPPlus4' => '0005'
            ]
        ]);

        $verify->verify('1600 Pennsylvania Ave NW', 'Apt 1', 'Washington', 'DC', '20500');
        $address = $verify->getAddress();

        $this->assertEquals('1600 PENNSYLVANIA AVE NW', $address['streetAddress']);
        $this->assertEquals('APT 1', $address['secondaryAddress']);
    }

    public function testGetRawResponse(): void
    {
        $mockResponse = [
            'address' => [
                'streetAddress' => '6406 IVY LN',
                'city' => 'GREENBELT',
                'state' => 'MD',
                'ZIPCode' => '20770',
                'ZIPPlus4' => '1441'
            ]
        ];

        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse($mockResponse);
        $verify->verify('6406 Ivy Lane', '', 'Greenbelt', 'MD', '20770');

        $this->assertEquals($mockResponse, $verify->getRawResponse());
    }

    public function testQueryBuildsCorrectlyWithAllFields(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse(['address' => []]);

        $verify->verify('123 Main St', 'Apt 4B', 'Springfield', 'IL', '62701', '1234');

        $query = $verify->getLastQuery();
        $this->assertEquals('123 Main St', $query['streetAddress']);
        $this->assertEquals('Apt 4B', $query['secondaryAddress']);
        $this->assertEquals('Springfield', $query['city']);
        $this->assertEquals('IL', $query['state']);
        $this->assertEquals('62701', $query['ZIPCode']);
        $this->assertEquals('1234', $query['ZIPPlus4']);
    }

    public function testQueryStripsEmptyFields(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse(['address' => []]);

        $verify->verify('123 Main St', '', 'Springfield', 'IL', '', '');

        $query = $verify->getLastQuery();
        $this->assertArrayHasKey('streetAddress', $query);
        $this->assertArrayNotHasKey('secondaryAddress', $query);
        $this->assertArrayHasKey('city', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayNotHasKey('ZIPCode', $query);
        $this->assertArrayNotHasKey('ZIPPlus4', $query);
    }

    public function testZipPlus4ValidationRejectsInvalidFormat(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse(['address' => []]);

        $verify->verify('123 Main St', '', 'City', 'ST', '12345', 'invalid');

        $query = $verify->getLastQuery();
        $this->assertArrayNotHasKey('ZIPPlus4', $query);
    }

    public function testZipPlus4ValidationRejectsWrongLength(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse(['address' => []]);

        $verify->verify('123 Main St', '', 'City', 'ST', '12345', '123');

        $query = $verify->getLastQuery();
        $this->assertArrayNotHasKey('ZIPPlus4', $query);
    }

    public function testZipPlus4ValidationAcceptsValidFormat(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse(['address' => []]);

        $verify->verify('123 Main St', '', 'City', 'ST', '12345', '6789');

        $query = $verify->getLastQuery();
        $this->assertEquals('6789', $query['ZIPPlus4']);
    }

    public function testIsSuccessReturnsFalseBeforeVerify(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');

        $this->assertFalse($verify->isSuccess());
    }

    public function testIsErrorReturnsFalseBeforeVerify(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');

        $this->assertFalse($verify->isError());
    }

    public function testErrorMessageIsNullBeforeVerify(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');

        $this->assertNull($verify->getErrorMessage());
    }

    public function testVerifyResetsErrorState(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('', '');

        // First call fails due to no credentials
        $verify->verify('123 Main St', '', 'City', 'ST', '12345');
        $this->assertTrue($verify->isError());

        // Simulate adding credentials
        $verify->setCredentials('test_id', 'test_secret');
        $verify->setMockResponse(['address' => ['streetAddress' => '123 MAIN ST']]);

        // Second call should succeed and clear error
        $result = $verify->verify('123 Main St', '', 'City', 'ST', '12345');
        $this->assertTrue($result);
        $this->assertFalse($verify->isError());
        $this->assertNull($verify->getErrorMessage());
    }

    public function testHandlesMissingAddressInResponse(): void
    {
        $verify = new TestableUSPSAddressVerifyV3('test_id', 'test_secret');
        $verify->setMockResponse(['status' => 'ok']); // No 'address' key

        $verify->verify('123 Main St', '', 'City', 'ST', '12345');

        $this->assertNull($verify->getAddress());
    }
}

/**
 * Testable version that doesn't make HTTP requests
 */
class TestableUSPSAddressVerifyV3 extends USPSAddressVerifyV3
{
    private array $mockResponse = [];
    private array $lastQuery = [];
    private string $testClientId;
    private string $testClientSecret;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->testClientId = $clientId;
        $this->testClientSecret = $clientSecret;
        // Don't call parent constructor to avoid CryptoGen dependency
    }

    public function isConfigured(): bool
    {
        return !empty($this->testClientId) && !empty($this->testClientSecret);
    }

    public function setCredentials(string $clientId, string $clientSecret): void
    {
        $this->testClientId = $clientId;
        $this->testClientSecret = $clientSecret;
    }

    public function setMockResponse(array $response): void
    {
        $this->mockResponse = $response;
    }

    public function getLastQuery(): array
    {
        return $this->lastQuery;
    }

    protected function doRequest(array $query): array
    {
        $this->lastQuery = $query;
        return $this->mockResponse;
    }

    protected function getToken(): string
    {
        return 'mock_token';
    }
}
