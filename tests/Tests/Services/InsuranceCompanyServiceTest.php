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
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Address\AddressData;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\PhoneType;
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
    #[Test]
    public function testBuildSaveDataFromFormMapsFormFields(): void
    {
        $data = $this->service->buildSaveDataFromForm([
            'form_id' => '4242',
            'form_name' => 'test-fixture-Mapped Insurance',
            'form_attn' => 'Claims',
            'form_cms_id' => '54321',
            'form_ins_type_code' => '2',
            'form_partner' => '7',
            'form_addr1' => '500 Mapping Way',
            'form_addr2' => 'Suite 9',
            'form_city' => 'Tampa',
            'form_state' => 'FL',
            'form_zip' => '33601',
            'form_country' => 'USA',
            'form_phone' => '8135551212',
            'form_cqm_sop' => '3111',
        ]);

        $this->assertEquals('test-fixture-Mapped Insurance', $data['name']);
        $this->assertEquals('Claims', $data['attn']);
        $this->assertEquals('54321', $data['cms_id']);
        $this->assertEquals('2', $data['ins_type_code']);
        $this->assertEquals('7', $data['x12_default_partner_id']);
        $this->assertEquals('500 Mapping Way', $data['line1']);
        $this->assertEquals('Suite 9', $data['line2']);
        $this->assertEquals('Tampa', $data['city']);
        $this->assertEquals('FL', $data['state']);
        $this->assertEquals('33601', $data['zip']);
        $this->assertEquals('USA', $data['country']);
        $this->assertEquals('8135551212', $data['phone']);
        $this->assertEquals('3111', $data['cqm_sop']);
        $this->assertNull($data['alt_cms_id']);
        // An explicit id with no "Save as New" => update target.
        $this->assertEquals('4242', $data['foreign_id']);
    }

    #[Test]
    public function testBuildSaveDataFromFormTreatsSaveAsNewAsInsert(): void
    {
        $data = $this->service->buildSaveDataFromForm([
            'form_save' => 'Save as New',
            'form_id' => '4242', // present, but "Save as New" must win
            'form_name' => 'test-fixture-New Insurance',
        ]);

        // "Save as New" must clear the foreign id so a fresh row is created.
        $this->assertEquals('', $data['foreign_id']);
        $this->assertEquals('test-fixture-New Insurance', $data['name']);
    }

    #[Test]
    public function testBuildSaveDataFromFormDefaultsMissingFields(): void
    {
        $data = $this->service->buildSaveDataFromForm([]);

        $this->assertEquals('', $data['name']);
        $this->assertEquals('', $data['foreign_id']); // empty form_id => insert
        $this->assertNull($data['x12_receiver_id']);
        $this->assertNull($data['alt_cms_id']);
    }

    #[Test]
    public function testSaveFromFormInsertsNewCompany(): void
    {
        $result = $this->service->saveFromForm([
            'form_save' => 'Save as New',
            'form_name' => 'test-fixture-SaveFromForm Insert',
            'form_attn' => 'Claims',
            'form_cms_id' => '12121',
            'form_ins_type_code' => '1',
            'form_addr1' => '742 Evergreen Terrace',
            'form_city' => 'Springfield',
            'form_state' => 'IL',
            'form_zip' => '62701',
            'form_country' => 'USA',
        ]);

        $id = $result['id'];
        $this->createdIds[] = $id;

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        $this->assertIsString($result['name']);

        /** @var array{name: string}|false $row */
        $row = QueryUtils::querySingleRow(
            "SELECT name FROM insurance_companies WHERE id = ?",
            [$id]
        );
        $this->assertNotFalse($row);
        $this->assertEquals('test-fixture-SaveFromForm Insert', $row['name']);
    }

    #[Test]
    public function testSaveFromFormUpdatesExistingCompany(): void
    {
        $id = $this->insertAndTrack();

        $result = $this->service->saveFromForm([
            'form_update' => 'Update',
            'form_id' => (string) $id,
            'form_name' => 'test-fixture-SaveFromForm Updated',
            'form_attn' => 'Claims Department',
            'form_cms_id' => '88888',
            'form_ins_type_code' => '1',
            'form_addr1' => '100 Insurance Blvd',
            'form_city' => 'Springfield',
            'form_state' => 'IL',
            'form_zip' => '62701',
            'form_country' => 'USA',
        ]);

        $this->assertEquals($id, $result['id']);

        /** @var array{name: string}|false $row */
        $row = QueryUtils::querySingleRow(
            "SELECT name FROM insurance_companies WHERE id = ?",
            [$id]
        );
        $this->assertNotFalse($row);
        $this->assertEquals('test-fixture-SaveFromForm Updated', $row['name']);
    }

    #[Test]
    public function testSaveFromFormThrowsWhenUpdateTargetMissing(): void
    {
        // Updating a non-existent row triggers BaseService::update() returning
        // false, which saveFromForm() turns into a RuntimeException so the
        // caller doesn't have to branch on a stringly-typed error return.
        $this->expectException(\RuntimeException::class);

        $this->service->saveFromForm([
            'form_update' => 'Update',
            'form_id' => '0',
            'form_name' => 'test-fixture-SaveFromForm Missing',
            'form_addr1' => '1 Nowhere St',
            'form_city' => 'Nowhere',
            'form_state' => 'IL',
            'form_zip' => '60000',
            'form_country' => 'USA',
        ]);
    }

    #[Test]
    public function testLegacyPersistSkipsPhoneWithoutTenDigitNationalNumber(): void
    {
        // Regression for the TypeError on Practice Settings → Insurance Company
        // edit when the phone field posts something PhoneNumber::tryParse will
        // accept but that doesn't yield a 10-digit NANP national number
        // (e.g. "555-1234"). The legacy InsuranceCompany::persist() forwarded
        // a null from PhoneNumber::getNationalDigits() into
        // PhoneNumberService::getPhoneParts(string), throwing:
        //   TypeError: Argument #1 ($phone_number) must be of type string, null given
        $co = new \InsuranceCompany();
        $co->set_name('test-fixture-LegacyPersistShortPhone');
        $co->set_phone('555-1234');

        $co->persist();
        $insuranceId = $co->id;
        $this->assertTrue(is_int($insuranceId) || is_string($insuranceId));
        $this->createdIds[] = $insuranceId;

        $phoneRows = QueryUtils::fetchRecordsNoLog(
            "SELECT id FROM phone_numbers WHERE foreign_id = ?",
            [$insuranceId]
        );
        $this->assertSame([], $phoneRows);
    }

    #[Test]
    public function testLegacyPersistDoesNotDuplicatePhoneRowsOnResave(): void
    {
        // Regression for the duplicate phone_numbers rows that accumulated on
        // every save before persist() learned to clear by foreign_id first.
        // PR #10326 swapped the legacy upsert for a bare PhoneNumberService
        // insert(), so each save added another WORK row + another FAX row;
        // the list view's two LEFT JOINs on phone_numbers then multiplied the
        // company across (work_count * fax_count) rows in the UI.
        $co = new \InsuranceCompany();
        $co->set_name('test-fixture-LegacyPersistDuplicate');
        $co->set_phone('5551234567');
        $co->set_fax('5559876543');
        $co->persist();
        $insuranceId = $co->id;
        $this->assertTrue(is_int($insuranceId) || is_string($insuranceId));
        $this->createdIds[] = $insuranceId;

        // After first save: one WORK row + one FAX row.
        $afterCreate = QueryUtils::fetchRecordsNoLog(
            "SELECT id, type FROM phone_numbers WHERE foreign_id = ?",
            [$insuranceId]
        );
        $this->assertCount(2, $afterCreate);

        // Re-save unchanged. Without the fix this would double to four rows.
        $co->persist();
        $afterResave = QueryUtils::fetchRecordsNoLog(
            "SELECT id, type FROM phone_numbers WHERE foreign_id = ?",
            [$insuranceId]
        );
        $this->assertCount(2, $afterResave);

        // Re-save with a changed phone. The clear-then-insert should leave
        // exactly one row per type, the WORK row should carry the new number,
        // and the FAX row should still carry the original (since set_phone
        // touches only the WORK entry).
        $co->set_phone('5551112222');
        $co->persist();
        $afterEdit = QueryUtils::fetchRecordsNoLog(
            "SELECT area_code, prefix, number, type FROM phone_numbers"
            . " WHERE foreign_id = ?",
            [$insuranceId]
        );
        $this->assertCount(2, $afterEdit);
        $byType = [];
        foreach ($afterEdit as $row) {
            $rowType = $row['type'];
            $this->assertIsInt($rowType);
            $byType[$rowType] = $row;
        }
        $workRow = $byType[PhoneType::WORK->value] ?? null;
        $this->assertNotNull($workRow);
        $this->assertSame('555', $workRow['area_code']);
        $this->assertSame('111', $workRow['prefix']);
        $this->assertSame('2222', $workRow['number']);
        $faxRow = $byType[PhoneType::FAX->value] ?? null;
        $this->assertNotNull($faxRow);
        $this->assertSame('555', $faxRow['area_code']);
        $this->assertSame('987', $faxRow['prefix']);
        $this->assertSame('6543', $faxRow['number']);
    }
}
