<?php

/**
 * AddressService Integration Tests
 *
 * Tests AddressService methods against a live database using the
 * AddressData and AddressRecord DTOs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Address\AddressData;
use OpenEMR\Services\Address\AddressRecord;
use OpenEMR\Services\AddressService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AddressServiceTest extends TestCase
{
    private AddressService $addressService;

    /** @var list<int> Foreign IDs used in tests, for cleanup */
    private array $testForeignIds = [];

    protected function setUp(): void
    {
        $this->addressService = new AddressService();
    }

    protected function tearDown(): void
    {
        foreach ($this->testForeignIds as $foreignId) {
            QueryUtils::fetchRecordsNoLog(
                "DELETE FROM `addresses` WHERE `foreign_id` = ?",
                [$foreignId]
            );
        }
    }

    /**
     * Generate a unique foreign ID for test isolation.
     * Use the sequences table like InsuranceCompanyService does.
     */
    private function generateTestForeignId(): int
    {
        /** @var int $id */
        $id = QueryUtils::generateId();
        $this->testForeignIds[] = $id;
        return $id;
    }

    #[Test]
    public function testValidateAcceptsValidAddress(): void
    {
        $address = new AddressData(
            line1: '123 Main St',
            line2: 'Apt 4B',
            city: 'Springfield',
            state: 'IL',
            zip: '62701',
            country: 'USA',
        );

        $result = $this->addressService->validate($address);

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getMessages());
    }

    #[Test]
    public function testValidateAcceptsEmptyOptionalFields(): void
    {
        $address = new AddressData(
            line1: '',
            line2: '',
            city: '',
            state: '',
            zip: '',
            country: '',
        );

        $result = $this->addressService->validate($address);

        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function testValidateRejectsFieldTooShort(): void
    {
        $address = new AddressData(
            line1: 'X', // below minimum length of 2
            line2: '',
            city: 'Springfield',
            state: 'IL',
            zip: '62701',
            country: 'USA',
        );

        $result = $this->addressService->validate($address);

        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('line1', $messages);
    }

    #[Test]
    public function testValidateRejectsMultipleInvalidFields(): void
    {
        $address = new AddressData(
            line1: 'X',
            line2: '',
            city: 'Y',
            state: 'Z',
            zip: '1',
            country: 'A',
        );

        $result = $this->addressService->validate($address);

        $this->assertFalse($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('line1', $messages);
        $this->assertArrayHasKey('city', $messages);
        // state minimum is 2, 'Z' is only 1
        $this->assertArrayHasKey('state', $messages);
        $this->assertArrayHasKey('zip', $messages);
        $this->assertArrayHasKey('country', $messages);
    }

    #[Test]
    public function testInsertAndGetOneByForeignId(): void
    {
        $foreignId = $this->generateTestForeignId();
        $data = new AddressData(
            line1: '456 Oak Ave',
            line2: 'Floor 3',
            city: 'Chicago',
            state: 'IL',
            zip: '60601',
            country: 'USA',
            plusFour: '1234',
        );

        $addressId = $this->addressService->insert($data, $foreignId);

        $this->assertIsInt($addressId);
        $this->assertGreaterThan(0, $addressId);

        // Retrieve and verify round-trip
        $retrieved = $this->addressService->getOneByForeignId($foreignId);

        $this->assertInstanceOf(AddressData::class, $retrieved);
        $this->assertEquals('456 Oak Ave', $retrieved->line1);
        $this->assertEquals('Floor 3', $retrieved->line2);
        $this->assertEquals('Chicago', $retrieved->city);
        $this->assertEquals('IL', $retrieved->state);
        $this->assertEquals('60601', $retrieved->zip);
        $this->assertEquals('USA', $retrieved->country);
        $this->assertEquals('1234', $retrieved->plusFour);
    }

    #[Test]
    public function testInsertWithNullPlusFour(): void
    {
        $foreignId = $this->generateTestForeignId();
        $data = new AddressData(
            line1: '789 Pine Rd',
            line2: '',
            city: 'Boston',
            state: 'MA',
            zip: '02101',
            country: 'USA',
        );

        $addressId = $this->addressService->insert($data, $foreignId);

        $this->assertIsInt($addressId);

        $retrieved = $this->addressService->getOneByForeignId($foreignId);
        $this->assertInstanceOf(AddressData::class, $retrieved);
        $this->assertNull($retrieved->plusFour);
    }

    #[Test]
    public function testUpdateModifiesAddress(): void
    {
        $foreignId = $this->generateTestForeignId();
        $original = new AddressData(
            line1: '100 First St',
            line2: '',
            city: 'Austin',
            state: 'TX',
            zip: '73301',
            country: 'USA',
        );
        $this->addressService->insert($original, $foreignId);

        // Update with new data
        $updated = new AddressData(
            line1: '200 Second Ave',
            line2: 'Unit B',
            city: 'Dallas',
            state: 'TX',
            zip: '75201',
            country: 'USA',
            plusFour: '5678',
        );
        $resultId = $this->addressService->update($updated, $foreignId);

        $this->assertNotNull($resultId);

        // Verify update persisted
        $retrieved = $this->addressService->getOneByForeignId($foreignId);
        $this->assertInstanceOf(AddressData::class, $retrieved);
        $this->assertEquals('200 Second Ave', $retrieved->line1);
        $this->assertEquals('Unit B', $retrieved->line2);
        $this->assertEquals('Dallas', $retrieved->city);
        $this->assertEquals('75201', $retrieved->zip);
        $this->assertEquals('5678', $retrieved->plusFour);
    }

    #[Test]
    public function testGetOneByForeignIdReturnsNullWhenNotFound(): void
    {
        $result = $this->addressService->getOneByForeignId(999999999);

        $this->assertNull($result);
    }

    #[Test]
    public function testGetAddressFromRecordAsString(): void
    {
        $record = new AddressRecord(
            street: '123 Main St',
            city: 'Springfield',
            state: 'IL',
            postalCode: '62701',
            countryCode: 'USA',
        );

        $result = $this->addressService->getAddressFromRecordAsString($record);

        $this->assertEquals("123 Main St\nSpringfield, IL 62701 USA", $result);
    }

    #[Test]
    public function testGetAddressFromRecordAsStringPartialAddress(): void
    {
        $record = new AddressRecord(
            city: 'Springfield',
            state: 'IL',
        );

        $result = $this->addressService->getAddressFromRecordAsString($record);

        $this->assertEquals('Springfield, IL', $result);
    }

    #[Test]
    public function testGetAddressFromRecordAsStringEmptyAddress(): void
    {
        $record = new AddressRecord();

        $result = $this->addressService->getAddressFromRecordAsString($record);

        $this->assertEquals('', $result);
    }

    #[Test]
    public function testInsertWithFromArrayIntegration(): void
    {
        $foreignId = $this->generateTestForeignId();

        // Simulate how InsuranceCompanyService calls AddressService -
        // converting a mixed data array to AddressData via fromArray()
        $rawData = [
            'name' => 'Test Insurance Co',
            'line1' => '321 Insurance Way',
            'line2' => '',
            'city' => 'Hartford',
            'state' => 'CT',
            'zip' => '06101',
            'country' => 'USA',
            'plus_four' => '0001',
            'some_unrelated_field' => 'ignored',
        ];

        $addressData = AddressData::fromArray($rawData);
        $addressId = $this->addressService->insert($addressData, $foreignId);

        $this->assertIsInt($addressId);
        $this->assertGreaterThan(0, $addressId);

        $retrieved = $this->addressService->getOneByForeignId($foreignId);
        $this->assertInstanceOf(AddressData::class, $retrieved);
        $this->assertEquals('321 Insurance Way', $retrieved->line1);
        $this->assertEquals('Hartford', $retrieved->city);
        $this->assertEquals('CT', $retrieved->state);
        $this->assertEquals('06101', $retrieved->zip);
        $this->assertEquals('0001', $retrieved->plusFour);
    }

    #[Test]
    public function testValidateWithFromArrayIntegration(): void
    {
        // Simulate the InsuranceCompanyRestController path:
        // AddressData::fromArray($data) → AddressService::validate()
        $data = [
            'line1' => '123 Main St',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip' => '62701',
            'country' => 'USA',
        ];

        $addressData = AddressData::fromArray($data);
        $result = $this->addressService->validate($addressData);

        $this->assertTrue($result->isValid());
    }
}
