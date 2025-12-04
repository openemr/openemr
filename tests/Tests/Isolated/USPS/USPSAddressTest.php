<?php

/**
 * Isolated tests for USPSAddress class
 *
 * Tests the USPSAddress class functionality without requiring database connections
 * or external dependencies. Validates address field setting, method chaining,
 * field capitalization, and data retrieval operations.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Generated Tests
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\USPS;

use OpenEMR\USPS\USPSAddress;
use PHPUnit\Framework\TestCase;

class USPSAddressTest extends TestCase
{
    private USPSAddress $address;

    protected function setUp(): void
    {
        $this->address = new USPSAddress();
    }

    public function testSetAddress(): void
    {
        $result = $this->address->setAddress('123 Main St');

        $this->assertInstanceOf(USPSAddress::class, $result);
        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals('123 Main St', $addressInfo['Address2']);
    }

    public function testSetApt(): void
    {
        $result = $this->address->setApt('Apt 4B');

        $this->assertInstanceOf(USPSAddress::class, $result);
        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals('Apt 4B', $addressInfo['Address1']);
    }

    public function testSetCity(): void
    {
        $result = $this->address->setCity('Springfield');

        $this->assertInstanceOf(USPSAddress::class, $result);
        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals('Springfield', $addressInfo['City']);
    }

    public function testSetState(): void
    {
        $result = $this->address->setState('IL');

        $this->assertInstanceOf(USPSAddress::class, $result);
        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals('IL', $addressInfo['State']);
    }

    public function testSetZip4(): void
    {
        $result = $this->address->setZip4('1234');

        $this->assertInstanceOf(USPSAddress::class, $result);
        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals('1234', $addressInfo['Zip4']);
    }

    public function testSetZip5(): void
    {
        $result = $this->address->setZip5('62701');

        $this->assertInstanceOf(USPSAddress::class, $result);
        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals('62701', $addressInfo['Zip5']);
    }

    public function testSetFirmName(): void
    {
        $result = $this->address->setFirmName('Acme Corp');

        $this->assertInstanceOf(USPSAddress::class, $result);
        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals('Acme Corp', $addressInfo['FirmName']);
    }

    public function testSetField(): void
    {
        $result = $this->address->setField('customField', 'customValue');

        $this->assertInstanceOf(USPSAddress::class, $result);
        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals('customValue', $addressInfo['CustomField']);
    }

    public function testSetFieldCapitalizesKey(): void
    {
        $this->address->setField('test_field', 'value');

        $addressInfo = $this->address->getAddressInfo();
        $this->assertArrayHasKey('Test_field', $addressInfo);
        $this->assertEquals('value', $addressInfo['Test_field']);
    }

    public function testGetAddressInfoEmpty(): void
    {
        $addressInfo = $this->address->getAddressInfo();

        $this->assertIsArray($addressInfo);
        $this->assertEmpty($addressInfo);
    }

    public function testMethodChaining(): void
    {
        $result = $this->address
            ->setAddress('123 Main St')
            ->setCity('Springfield')
            ->setState('IL')
            ->setZip5('62701');

        $this->assertInstanceOf(USPSAddress::class, $result);

        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals('123 Main St', $addressInfo['Address2']);
        $this->assertEquals('Springfield', $addressInfo['City']);
        $this->assertEquals('IL', $addressInfo['State']);
        $this->assertEquals('62701', $addressInfo['Zip5']);
    }

    public function testCompleteAddressInfo(): void
    {
        $this->address
            ->setFirmName('Test Company')
            ->setApt('Suite 100')
            ->setAddress('456 Oak Avenue')
            ->setCity('Chicago')
            ->setState('IL')
            ->setZip5('60601')
            ->setZip4('1234');

        $addressInfo = $this->address->getAddressInfo();

        $this->assertCount(7, $addressInfo);
        $this->assertEquals('Test Company', $addressInfo['FirmName']);
        $this->assertEquals('Suite 100', $addressInfo['Address1']);
        $this->assertEquals('456 Oak Avenue', $addressInfo['Address2']);
        $this->assertEquals('Chicago', $addressInfo['City']);
        $this->assertEquals('IL', $addressInfo['State']);
        $this->assertEquals('60601', $addressInfo['Zip5']);
        $this->assertEquals('1234', $addressInfo['Zip4']);
    }

    public function testNumericValues(): void
    {
        $this->address
            ->setZip5(12345)
            ->setZip4(6789);

        $addressInfo = $this->address->getAddressInfo();
        $this->assertEquals(12345, $addressInfo['Zip5']);
        $this->assertEquals(6789, $addressInfo['Zip4']);
    }
}
