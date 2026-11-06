<?php

/**
 * addresses.id has no AUTO_INCREMENT. The Address object (insurance company and pharmacy screens, patient addresses)
 * takes the next number of the sequences counter and saves with INSERT ... ON DUPLICATE KEY UPDATE, while
 * AddressService and ContactRelationService took MAX(id)+1. An id taken that way could be the counter's next
 * number, and the next Address save then overwrote that row with another owner's address.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\ORDataObject\Address;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Address\AddressData;
use OpenEMR\Services\AddressService;
use OpenEMR\Services\ContactRelationService;
use PHPUnit\Framework\TestCase;

class AddressIdAllocationTest extends TestCase
{
    /** Owners no real row has: the foreign_id of each synthetic address. */
    private const OWNER_SCREEN = 990000331;
    private const OWNER_SERVICE = 990000332;
    private const OWNER_NEXT = 990000333;
    private const PID = 990000334;

    private ?int $personId = null;

    protected function setUp(): void
    {
        $this->cleanUp();
        // An id taken by MAX(id)+1 before this test would be above the counter, and the first Address save below
        // could land on it. Move the counter past them, as the upgrade does.
        QueryUtils::sqlStatementThrowException(
            "UPDATE `sequences` SET `id` = (SELECT MAX(`id`) FROM `addresses`) WHERE `id` < (SELECT MAX(`id`) FROM `addresses`)"
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        $left = QueryUtils::fetchSingleValue(
            "SELECT (SELECT COUNT(*) FROM addresses WHERE foreign_id IN (?, ?, ?) OR line1 LIKE 'test-fixture-address-id%')"
            . " + (SELECT COUNT(*) FROM person WHERE first_name = 'test-fixture-address-id')"
            . " + (SELECT COUNT(*) FROM contact WHERE foreign_table_name = 'patient_data' AND foreign_id = ?)"
            . " + (SELECT COUNT(*) FROM patient_data WHERE pid = ?) AS n",
            'n',
            [self::OWNER_SCREEN, self::OWNER_SERVICE, self::OWNER_NEXT, self::PID, self::PID]
        );
        $this->assertEquals(0, $left, 'the synthetic rows are removed');
    }

    public function testAnInsurersAddressKeepsItsOwnAddressAfterTheNextScreenSave(): void
    {
        $this->saveAsTheScreensDo(self::OWNER_SCREEN, 'test-fixture-address-id screen');
        // As InsuranceCompanyService::update() does for a company saved without an address.
        $serviceId = (new AddressService())->insert(
            new AddressData('test-fixture-address-id service', '', 'Testville', 'VT', '05001', 'USA'),
            self::OWNER_SERVICE
        );
        $this->assertIsInt($serviceId);
        // A patient's new address, saved with no foreign_id, as ContactAddressService saves it.
        $this->saveAsTheScreensDo(null, 'test-fixture-address-id patient');

        $this->assertSame(
            ['foreign_id' => self::OWNER_SERVICE, 'line1' => 'test-fixture-address-id service'],
            $this->addressRow($serviceId)
        );
        $this->assertSame(3, $this->syntheticAddressCount(), 'three saves, three rows');
    }

    public function testARelatedPersonsAddressKeepsItsOwnerAfterTheNextScreenSave(): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'test-fixture-address-id', 'test-fixture-address-id', NOW())",
            [self::PID, random_bytes(16)]
        );
        $service = new ContactRelationService();
        $created = $service->insertRelatedPerson([
            'pid' => self::PID,
            'relationship' => 'SPS',
            'first_name' => 'test-fixture-address-id',
            'last_name' => 'test-fixture-address-id',
        ]);
        $this->assertTrue($created->isValid() && $created->hasData(), 'the related person is created');
        $personUuid = QueryUtils::fetchSingleValue("SELECT uuid FROM person WHERE first_name = 'test-fixture-address-id'", 'uuid');
        $this->assertIsString($personUuid);
        $personId = QueryUtils::fetchSingleValue("SELECT id FROM person WHERE first_name = 'test-fixture-address-id'", 'id');
        $this->assertIsNumeric($personId);
        $this->personId = (int) $personId;

        $this->saveAsTheScreensDo(self::OWNER_SCREEN, 'test-fixture-address-id screen');
        // A FHIR RelatedPerson PUT replaces the person's addresses.
        $updated = $service->updateRelatedPerson(
            UuidRegistry::uuidToString($personUuid),
            ['addresses' => [['line1' => 'test-fixture-address-id related', 'city' => 'Testville', 'state' => 'VT']]],
            self::PID
        );
        $this->assertTrue($updated->isValid(), 'the related person is updated');
        $relatedId = QueryUtils::fetchSingleValue(
            "SELECT ca.address_id FROM contact_address ca JOIN contact c ON c.id = ca.contact_id"
            . " WHERE c.foreign_table_name = 'person' AND c.foreign_id = ?",
            'address_id',
            [$this->personId]
        );
        $this->assertIsNumeric($relatedId);
        $this->saveAsTheScreensDo(self::OWNER_NEXT, 'test-fixture-address-id next');

        $this->assertSame(
            ['foreign_id' => null, 'line1' => 'test-fixture-address-id related'],
            $this->addressRow((int) $relatedId)
        );
        $this->assertSame(3, $this->syntheticAddressCount(), 'three saves, three rows');
    }

    public function testNoAddressIsSavedWhenTheCounterGivesNoId(): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, uuid, fname, lname, `date`) VALUES (?, ?, 'test-fixture-address-id', 'test-fixture-address-id', NOW())",
            [self::PID, random_bytes(16)]
        );
        $service = new ContactRelationService();
        $created = $service->insertRelatedPerson([
            'pid' => self::PID,
            'relationship' => 'SPS',
            'first_name' => 'test-fixture-address-id',
            'last_name' => 'test-fixture-address-id',
        ]);
        $this->assertTrue($created->isValid() && $created->hasData(), 'the related person is created');
        $personUuid = QueryUtils::fetchSingleValue("SELECT uuid FROM person WHERE first_name = 'test-fixture-address-id'", 'uuid');
        $this->assertIsString($personUuid);

        // GenID() returns false with hasGenID off, and 0 when it cannot update the counter: no id either way.
        $adodb = OEGlobalsBag::getInstance()->get('adodb');
        $this->assertIsArray($adodb);
        $db = $adodb['db'];
        $this->assertInstanceOf(\ADOConnection::class, $db);
        $db->hasGenID = false;
        try {
            $updated = $service->updateRelatedPerson(
                UuidRegistry::uuidToString($personUuid),
                ['addresses' => [['line1' => 'test-fixture-address-id related', 'city' => 'Testville', 'state' => 'VT']]],
                self::PID
            );
            $this->assertTrue($updated->hasErrors(), 'the related person update fails');
            $serviceFailed = false;
            try {
                (new AddressService())->insert(
                    new AddressData('test-fixture-address-id service', '', 'Testville', 'VT', '05001', 'USA'),
                    self::OWNER_SERVICE
                );
            } catch (\RuntimeException) {
                $serviceFailed = true;
            }
            $this->assertTrue($serviceFailed, 'the address service refuses the insert');
        } finally {
            $db->hasGenID = true;
        }
        $this->assertSame(0, $this->syntheticAddressCount(), 'no address saved');
    }

    /**
     * A new address saved through the Address object, as the insurance company and pharmacy screens and a
     * patient's addresses are.
     */
    private function saveAsTheScreensDo(?int $owner, string $line1): void
    {
        $address = new Address();
        $address->setThrowExceptionOnError(true);
        $address->set_foreign_id($owner);
        $address->set_line1($line1);
        $address->set_city('Testville');
        $address->persist();
    }

    /**
     * @return array{foreign_id: int|null, line1: string}|null
     */
    private function addressRow(int $id): ?array
    {
        $row = QueryUtils::querySingleRow("SELECT foreign_id, line1 FROM addresses WHERE id = ?", [$id]);
        if (!is_array($row)) {
            return null;
        }
        return [
            'foreign_id' => is_numeric($row['foreign_id']) ? (int) $row['foreign_id'] : null,
            'line1' => is_string($row['line1']) ? $row['line1'] : '',
        ];
    }

    private function syntheticAddressCount(): int
    {
        $count = QueryUtils::fetchSingleValue("SELECT COUNT(*) AS n FROM addresses WHERE line1 LIKE 'test-fixture-address-id%'", 'n');
        $this->assertIsNumeric($count);
        return (int) $count;
    }

    private function cleanUp(): void
    {
        $personIds = QueryUtils::fetchTableColumn("SELECT id FROM person WHERE first_name = 'test-fixture-address-id'", 'id');
        $contactIds = QueryUtils::fetchTableColumn(
            "SELECT id FROM contact WHERE foreign_table_name = 'patient_data' AND foreign_id = ?",
            'id',
            [self::PID]
        );
        foreach ($personIds as $personId) {
            $contactIds = array_merge($contactIds, QueryUtils::fetchTableColumn(
                "SELECT id FROM contact WHERE foreign_table_name = 'person' AND foreign_id = ?",
                'id',
                [$personId]
            ));
        }
        foreach ($contactIds as $contactId) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM addresses WHERE id IN (SELECT address_id FROM contact_address WHERE contact_id = ?)",
                [$contactId]
            );
            foreach (['contact_address', 'contact_telecom', 'contact_relation'] as $table) {
                QueryUtils::sqlStatementThrowException("DELETE FROM `$table` WHERE contact_id = ?", [$contactId]);
            }
            QueryUtils::sqlStatementThrowException("DELETE FROM contact WHERE id = ?", [$contactId]);
        }
        foreach ($personIds as $personId) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM uuid_registry WHERE table_name = 'person' AND uuid = (SELECT uuid FROM person WHERE id = ?)",
                [$personId]
            );
            QueryUtils::sqlStatementThrowException("DELETE FROM person WHERE id = ?", [$personId]);
        }
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM addresses WHERE foreign_id IN (?, ?, ?) OR line1 LIKE 'test-fixture-address-id%'",
            [self::OWNER_SCREEN, self::OWNER_SERVICE, self::OWNER_NEXT]
        );
        QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [self::PID]);
    }
}
