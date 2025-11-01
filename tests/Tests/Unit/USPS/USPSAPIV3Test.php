<?php

/**
 * USPS API v3 Unit Tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    OpenEMR Contributors
 * @copyright Copyright (c) 2025 OpenEMR <dev@open-emr.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\USPS;

use PHPUnit\Framework\TestCase;
use OpenEMR\USPS\USPSAddress;
use OpenEMR\USPS\USPSAddressVerify;
use OpenEMR\USPS\USPSBase;

class USPSAPIV3Test extends TestCase
{
    // Test API version detection logic
    public function testAPIVersionDetection(): void
    {
        // legacy only
        $legacyAPI = new USPSAddressVerify('username123', '', '');
        $reflection = new \ReflectionClass($legacyAPI);
        $property = $reflection->getProperty('useV3');
        $this->assertFalse($property->getValue($legacyAPI));

        // v3 credentials
        $v3API = new USPSAddressVerify('', 'client_id', 'client_secret');
        $this->assertTrue($property->getValue($v3API));

        // both - v3 should win
        $bothAPI = new USPSAddressVerify('username123', 'client_id', 'client_secret');
        $this->assertTrue($property->getValue($bothAPI));
    }

    public function testAPIV3Endpoint(): void
    {
        $api = new USPSAddressVerify('', 'client_id', 'client_secret');
        $this->assertEquals('https://apis.usps.com/addresses/v3', $api->getEndpoint());
    }

    public function testLegacyEndpoint(): void
    {
        $api = new USPSAddressVerify('username123', '', '');
        $this->assertEquals('https://secure.shippingapis.com/ShippingAPI.dll', $api->getEndpoint());
    }

    public function testAddressCreation(): void
    {
        $address = new USPSAddress();
        $address->setAddress('6406 Ivy Lane');
        $address->setApt('APT 4');
        $address->setCity('Greenbelt');
        $address->setState('MD');
        $address->setZip5('20770');
        $address->setZip4('1441');

        $info = $address->getAddressInfo();

        $this->assertEquals('6406 Ivy Lane', $info['Address2']);
        $this->assertEquals('APT 4', $info['Address1']);
        $this->assertEquals('Greenbelt', $info['City']);
        $this->assertEquals('MD', $info['State']);
        $this->assertEquals('20770', $info['Zip5']);
        $this->assertEquals('1441', $info['Zip4']);
    }

    public function testAddAddressToVerify(): void
    {
        $verify = new USPSAddressVerify('', 'client_id', 'client_secret');
        $address = new USPSAddress();
        $address->setAddress('1600 Pennsylvania Ave NW');
        $address->setCity('Washington');
        $address->setState('DC');
        $address->setZip5('20500');

        $verify->addAddress($address);
        $fields = $verify->getPostFields();

        $this->assertArrayHasKey('Address', $fields);
        $this->assertCount(1, $fields['Address']);
        $this->assertEquals('1600 Pennsylvania Ave NW', $fields['Address'][0]['Address2']);
    }

    public function testAddMultipleAddresses(): void
    {
        $verify = new USPSAddressVerify('username123', '', '');

        $addr1 = new USPSAddress();
        $addr1->setAddress('1600 Pennsylvania Ave NW');
        $addr1->setCity('Washington');
        $addr1->setState('DC');

        $addr2 = new USPSAddress();
        $addr2->setAddress('6406 Ivy Lane');
        $addr2->setCity('Greenbelt');
        $addr2->setState('MD');

        $verify->addAddress($addr1);
        $verify->addAddress($addr2);

        $this->assertCount(2, $verify->getPostFields()['Address']);
    }

    // Check that address fields map correctly
    public function testV3ParameterMapping(): void
    {
        $verify = new USPSAddressVerify('', 'client_id', 'client_secret');
        $address = new USPSAddress();
        $address->setFirmName('ACME Corp');
        $address->setApt('Suite 100');
        $address->setAddress('123 Main St');
        $address->setCity('Springfield');
        $address->setState('IL');
        $address->setZip5('62701');
        $address->setZip4('1234');

        $verify->addAddress($address);
        $data = $verify->getPostFields()['Address'][0];

        $this->assertEquals('ACME Corp', $data['FirmName']);
        $this->assertEquals('Suite 100', $data['Address1']);
        $this->assertEquals('123 Main St', $data['Address2']);
        $this->assertEquals('Springfield', $data['City']);
        $this->assertEquals('IL', $data['State']);
        $this->assertEquals('62701', $data['Zip5']);
    }

    public function testConstructorBackwardCompatibility(): void
    {
        // old style still works
        $legacy = new USPSAddressVerify('myusername');
        $this->assertInstanceOf(USPSAddressVerify::class, $legacy);

        // new style
        $v3 = new USPSAddressVerify('', 'client_id', 'client_secret');
        $this->assertInstanceOf(USPSAddressVerify::class, $v3);

        // mixed - v3 takes priority
        $mixed = new USPSAddressVerify('myusername', 'client_id', 'client_secret');
        $this->assertInstanceOf(USPSAddressVerify::class, $mixed);
    }

    public function testErrorHandling(): void
    {
        $verify = new USPSAddressVerify('', 'bad_id', 'bad_secret');

        $reflection = new \ReflectionClass($verify);
        $method = $reflection->getMethod('fetchAccessToken');

        $result = $method->invoke($verify);

        $this->assertFalse($result);
        $this->assertNotEmpty($verify->getErrorMessage());
    }

    public function testResponseFormatDetection(): void
    {
        $verify = new USPSAddressVerify('', 'client_id', 'client_secret');

        $json = json_encode([
            'address' => [
                'streetAddress' => '6406 IVY LN',
                'city' => 'GREENBELT',
                'state' => 'MD',
                'ZIPCode' => '20770'
            ]
        ]);

        $verify->setResponse($json);
        $verify->setArrayResponse(json_decode($json, true));

        $resp = $verify->getArrayResponse();
        $this->assertArrayHasKey('address', $resp);
        $this->assertEquals('6406 IVY LN', $resp['address']['streetAddress']);
    }

    public function testVerifyRoutesCorrectly(): void
    {
        $mock = $this->getMockBuilder(USPSAddressVerify::class)
            ->setConstructorArgs(['username', '', ''])
            ->onlyMethods(['doRequest'])
            ->getMock();

        $mock->expects($this->once())->method('doRequest');

        $addr = new USPSAddress();
        $addr->setAddress('Test St');
        $addr->setCity('Test City');
        $addr->setState('TS');
        $mock->addAddress($addr);

        $mock->verify();
    }
}
