<?php

/**
 * InsuranceCompanyService Integration Tests
 *
 * Tests InsuranceCompanyService insert and update methods, verifying
 * that AddressData DTO integration works correctly when creating and
 * modifying insurance companies with address records.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\Address\AddressData;
use OpenEMR\Services\InsuranceCompanyService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class InsuranceCompanyServiceTest extends TestCase
{
    private InsuranceCompanyService $service;
    private AddressService $addressService;

    /** @var list<int|string> */
    private array $createdIds = [];

    protected function setUp(): void
    {
        $this->service = new InsuranceCompanyService();
        $this->addressService = new AddressService();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdIds as $id) {
            QueryUtils::fetchRecordsNoLog("DELETE FROM `addresses` WHERE `foreign_id` = ?", [$id]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `phone_numbers` WHERE `foreign_id` = ?", [$id]);
            QueryUtils::fetchRecordsNoLog("DELETE FROM `insurance_companies` WHERE `id` = ?", [$id]);
        }
    }

    /**
     * @return array<string, string>
     */
    private function getTestData(): array
    {
        return [
            'name' => 'test-fixture-Service Test Insurance',
            'attn' => 'Claims Department',
            'cms_id' => '88888',
            'ins_type_code' => '1',
            'x12_receiver_id' => '',
            'x12_default_partner_id' => '',
            'alt_cms_id' => '',
            'line1' => '100 Insurance Blvd',
            'line2' => 'Suite 200',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip' => '62701',
            'country' => 'USA',
        ];
    }

    /**
     * Insert test data and track the ID for cleanup.
     *
     * @return int|string
     */
    private function insertAndTrack(): int|string
    {
        /** @var int|string $id */
        $id = $this->service->insert($this->getTestData());
        $this->createdIds[] = $id;
        return $id;
    }

    #[Test]
    public function testInsertCreatesInsuranceCompany(): void
    {
        $id = $this->insertAndTrack();

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        /** @var array{name: string, cms_id: string}|false $row */
        $row = QueryUtils::querySingleRow(
            "SELECT name, cms_id FROM insurance_companies WHERE id = ?",
            [$id]
        );
        $this->assertNotFalse($row);
        $this->assertEquals('test-fixture-Service Test Insurance', $row['name']);
        $this->assertEquals('88888', $row['cms_id']);
    }

    #[Test]
    public function testInsertCreatesAddressRecord(): void
    {
        $id = $this->insertAndTrack();

        // The insert path calls AddressData::fromArray($data) then
        // addressService->insert() - verify the address was stored
        /** @var int $intId */
        $intId = $id;
        $address = $this->addressService->getOneByForeignId($intId);

        $this->assertInstanceOf(AddressData::class, $address);
        $this->assertEquals('100 Insurance Blvd', $address->line1);
        $this->assertEquals('Suite 200', $address->line2);
        $this->assertEquals('Springfield', $address->city);
        $this->assertEquals('IL', $address->state);
        $this->assertEquals('62701', $address->zip);
        $this->assertEquals('USA', $address->country);
    }

    #[Test]
    public function testInsertSkipsAddressWhenCityAndStateMissing(): void
    {
        $data = $this->getTestData();
        $data['city'] = '';
        $data['state'] = '';

        /** @var int|string $id */
        $id = $this->service->insert($data);
        $this->createdIds[] = $id;

        // Address should not be created when city and state are empty
        /** @var int $intId */
        $intId = $id;
        $address = $this->addressService->getOneByForeignId($intId);
        $this->assertNull($address);
    }

    #[Test]
    public function testUpdateModifiesInsuranceCompany(): void
    {
        $id = $this->insertAndTrack();

        $data = $this->getTestData();
        $data['name'] = 'test-fixture-Updated Insurance Co';
        $data['line1'] = '200 Updated Ave';
        $data['city'] = 'Chicago';
        $data['zip'] = '60601';

        $result = $this->service->update($data, $id);

        $this->assertEquals($id, $result);

        /** @var array{name: string}|false $row */
        $row = QueryUtils::querySingleRow(
            "SELECT name FROM insurance_companies WHERE id = ?",
            [$id]
        );
        $this->assertNotFalse($row);
        $this->assertEquals('test-fixture-Updated Insurance Co', $row['name']);
    }

    #[Test]
    public function testUpdateModifiesAddressRecord(): void
    {
        $id = $this->insertAndTrack();

        $data = $this->getTestData();
        $data['line1'] = '200 Updated Ave';
        $data['line2'] = 'Floor 5';
        $data['city'] = 'Chicago';
        $data['state'] = 'IL';
        $data['zip'] = '60601';
        $data['country'] = 'US';

        $this->service->update($data, $id);

        // The update path calls AddressData::fromArray($data) then
        // addressService->update() - verify the address was modified
        /** @var int $intId */
        $intId = $id;
        $address = $this->addressService->getOneByForeignId($intId);

        $this->assertInstanceOf(AddressData::class, $address);
        $this->assertEquals('200 Updated Ave', $address->line1);
        $this->assertEquals('Floor 5', $address->line2);
        $this->assertEquals('Chicago', $address->city);
        $this->assertEquals('60601', $address->zip);
        $this->assertEquals('US', $address->country);
    }

    #[Test]
    public function testGetOneByIdReturnsCompanyData(): void
    {
        $id = $this->insertAndTrack();

        $result = $this->service->getOneById($id);

        $this->assertIsArray($result);
        $this->assertEquals($id, $result['id']);
        $this->assertEquals('test-fixture-Service Test Insurance', $result['name']);
        $this->assertEquals('88888', $result['cms_id']);
    }

    #[Test]
    public function testSearchReturnsInsuranceCompanyWithAddressJoin(): void
    {
        $id = $this->insertAndTrack();

        $result = $this->service->search(['id' => $id]);

        $this->assertTrue($result->hasData());
        /** @var list<array<string, mixed>> $records */
        $records = $result->getData();
        $this->assertCount(1, $records);

        $record = $records[0];
        $this->assertEquals('test-fixture-Service Test Insurance', $record['name']);
        // Address fields should be joined from the addresses table
        $this->assertEquals('100 Insurance Blvd', $record['line1']);
        $this->assertEquals('Suite 200', $record['line2']);
        $this->assertEquals('Springfield', $record['city']);
        $this->assertEquals('IL', $record['state']);
        $this->assertEquals('62701', $record['zip']);
        $this->assertEquals('USA', $record['country']);
    }

    #[Test]
    public function testInsertWithExplicitId(): void
    {
        $data = $this->getTestData();
        $data['id'] = 9999999;

        /** @var int|string $id */
        $id = $this->service->insert($data);
        $this->createdIds[] = $id;

        $this->assertEquals(9999999, $id);

        /** @var array{id: int}|false $row */
        $row = QueryUtils::querySingleRow(
            "SELECT id FROM insurance_companies WHERE id = ?",
            [$id]
        );
        $this->assertNotFalse($row);
        $this->assertEquals(9999999, $row['id']);
    }
}
