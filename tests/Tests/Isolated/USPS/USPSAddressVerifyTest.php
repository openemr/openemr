<?php

/**
 * Isolated tests for USPSAddressVerify class
 *
 * Tests the USPSAddressVerify class functionality without requiring database connections
 * or external dependencies. Validates address verification setup, address management,
 * ID generation, API configuration, and inheritance behavior.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Generated Tests
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\USPS;

use OpenEMR\USPS\USPSAddress;
use OpenEMR\USPS\USPSAddressVerify;
use PHPUnit\Framework\TestCase;

class USPSAddressVerifyTest extends TestCase
{
    private USPSAddressVerify $addressVerify;

    protected function setUp(): void
    {
        $this->addressVerify = new USPSAddressVerify('testuser');
    }

    public function testConstructorSetsUsername(): void
    {
        $verify = new USPSAddressVerify('myusername');
        $postData = $verify->getPostData();
        $this->assertNotFalse(strpos((string) $postData['XML'], 'myusername'));
    }

    public function testApiVersionIsSetCorrectly(): void
    {
        $postData = $this->addressVerify->getPostData();
        $this->assertEquals('Verify', $postData['API']);
    }

    public function testGetResponseApiName(): void
    {
        $responseApiName = $this->addressVerify->getResponseApiName();
        $this->assertEquals('AddressValidateResponse', $responseApiName);
    }

    public function testAddAddressWithoutId(): void
    {
        $address = new USPSAddress();
        $address->setAddress('123 Main St')
               ->setCity('Springfield')
               ->setState('IL')
               ->setZip5('62701');

        $this->addressVerify->addAddress($address);

        $postFields = $this->addressVerify->getPostFields();
        $this->assertIsArray($postFields);
        $this->assertArrayHasKey('Address', $postFields);
        $this->assertCount(1, $postFields['Address']);

        $addedAddress = $postFields['Address'][0];
        $this->assertArrayHasKey('@attributes', $addedAddress);
        $this->assertEquals(1, $addedAddress['@attributes']['ID']);
        $this->assertEquals('123 Main St', $addedAddress['Address2']);
        $this->assertEquals('Springfield', $addedAddress['City']);
        $this->assertEquals('IL', $addedAddress['State']);
        $this->assertEquals('62701', $addedAddress['Zip5']);
    }

    public function testAddAddressWithCustomId(): void
    {
        $address = new USPSAddress();
        $address->setAddress('456 Oak Ave')
               ->setCity('Chicago')
               ->setState('IL');

        $this->addressVerify->addAddress($address, 'custom123');

        $postFields = $this->addressVerify->getPostFields();
        $addedAddress = $postFields['Address'][0];

        $this->assertEquals('custom123', $addedAddress['@attributes']['ID']);
        $this->assertEquals('456 Oak Ave', $addedAddress['Address2']);
        $this->assertEquals('Chicago', $addedAddress['City']);
        $this->assertEquals('IL', $addedAddress['State']);
    }

    public function testAddMultipleAddresses(): void
    {
        $address1 = new USPSAddress();
        $address1->setAddress('123 First St')->setCity('Springfield')->setState('IL');

        $address2 = new USPSAddress();
        $address2->setAddress('456 Second Ave')->setCity('Chicago')->setState('IL');

        $address3 = new USPSAddress();
        $address3->setAddress('789 Third Blvd')->setCity('Peoria')->setState('IL');

        $this->addressVerify->addAddress($address1);
        $this->addressVerify->addAddress($address2, 'custom2');
        $this->addressVerify->addAddress($address3);

        $postFields = $this->addressVerify->getPostFields();
        $this->assertCount(3, $postFields['Address']);

        // Check auto-generated IDs
        $this->assertEquals(1, $postFields['Address'][0]['@attributes']['ID']);
        $this->assertEquals('custom2', $postFields['Address'][1]['@attributes']['ID']);
        $this->assertEquals(2, $postFields['Address'][2]['@attributes']['ID']);

        // Check addresses
        $this->assertEquals('123 First St', $postFields['Address'][0]['Address2']);
        $this->assertEquals('456 Second Ave', $postFields['Address'][1]['Address2']);
        $this->assertEquals('789 Third Blvd', $postFields['Address'][2]['Address2']);
    }

    public function testAddAddressWithCompleteInfo(): void
    {
        $address = new USPSAddress();
        $address->setFirmName('Acme Corp')
               ->setApt('Suite 100')
               ->setAddress('123 Business Plaza')
               ->setCity('Springfield')
               ->setState('IL')
               ->setZip5('62701')
               ->setZip4('1234');

        $this->addressVerify->addAddress($address, 'business1');

        $postFields = $this->addressVerify->getPostFields();
        $addedAddress = $postFields['Address'][0];

        $this->assertEquals('business1', $addedAddress['@attributes']['ID']);
        $this->assertEquals('Acme Corp', $addedAddress['FirmName']);
        $this->assertEquals('Suite 100', $addedAddress['Address1']);
        $this->assertEquals('123 Business Plaza', $addedAddress['Address2']);
        $this->assertEquals('Springfield', $addedAddress['City']);
        $this->assertEquals('IL', $addedAddress['State']);
        $this->assertEquals('62701', $addedAddress['Zip5']);
        $this->assertEquals('1234', $addedAddress['Zip4']);
    }

    public function testGetPostFieldsEmpty(): void
    {
        $postFields = $this->addressVerify->getPostFields();
        $this->assertIsArray($postFields);
        $this->assertEmpty($postFields);
    }

    public function testGetPostDataIncludesAddresses(): void
    {
        $address = new USPSAddress();
        $address->setAddress('123 Test St')->setCity('Test City')->setState('TX');

        $this->addressVerify->addAddress($address);
        $postData = $this->addressVerify->getPostData();

        $this->assertArrayHasKey('API', $postData);
        $this->assertArrayHasKey('XML', $postData);
        $this->assertEquals('Verify', $postData['API']);

        // Verify XML contains address data
        $xml = $postData['XML'];
        $this->assertNotFalse(strpos((string) $xml, '123 Test St'));
        $this->assertNotFalse(strpos((string) $xml, 'Test City'));
        $this->assertNotFalse(strpos((string) $xml, 'TX'));
        $this->assertNotFalse(strpos((string) $xml, 'testuser'));
    }

    public function testVerifyMethodReturnsResponse(): void
    {
        // Create a testable version that doesn't make HTTP requests
        $testVerify = new TestableUSPSAddressVerify('testuser');
        $testVerify->setMockResponse('<xml>mock response</xml>');

        $result = $testVerify->verify();
        $this->assertEquals('<xml>mock response</xml>', $result);
    }

    public function testInheritanceFromUSPSBase(): void
    {
        $this->assertInstanceOf(\OpenEMR\USPS\USPSBase::class, $this->addressVerify);
    }

    public function testIdIncrementsCorrectly(): void
    {
        $address1 = new USPSAddress();
        $address1->setAddress('First');

        $address2 = new USPSAddress();
        $address2->setAddress('Second');

        $address3 = new USPSAddress();
        $address3->setAddress('Third');

        // Add addresses without specifying IDs
        $this->addressVerify->addAddress($address1);
        $this->addressVerify->addAddress($address2);
        $this->addressVerify->addAddress($address3);

        $postFields = $this->addressVerify->getPostFields();

        $this->assertEquals(1, $postFields['Address'][0]['@attributes']['ID']);
        $this->assertEquals(2, $postFields['Address'][1]['@attributes']['ID']);
        $this->assertEquals(2, $postFields['Address'][2]['@attributes']['ID']);
    }

    public function testNumericIdHandling(): void
    {
        $address = new USPSAddress();
        $address->setAddress('Test Address');

        $this->addressVerify->addAddress($address, 999);

        $postFields = $this->addressVerify->getPostFields();
        $this->assertEquals(999, $postFields['Address'][0]['@attributes']['ID']);
    }
}

// Testable version that doesn't make HTTP requests
class TestableUSPSAddressVerify extends USPSAddressVerify
{
    private string $mockResponse = '';

    public function setMockResponse(string $response): void
    {
        $this->mockResponse = $response;
    }

    protected function doRequest($ch = null): string
    {
        return $this->mockResponse;
    }
}
