<?php

namespace OpenEMR\Tests\Fixtures;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Uuid;

/**
 * Provides OpenEMR Fixtures/Sample Records to test cases as Objects or Database Records.
 *
 * The FixtureManager generates sample records from JSON files located within the Fixture namespace.
 * To provide support for additional record types:
 * - Add a JSON datafile to the Fixture namespace containing the sample records.
 * - Add public methods to get, install, and remove fixture records.
 * - The "patient" related methods provide clear working examples.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FixtureManager
{
    // use a prefix so we can easily remove fixtures
    const PATIENT_FIXTURE_PUBPID_PREFIX = "test-fixture";

    /** @var array<string, mixed>[] */
    private readonly array $patientFixtures;
    private $fhirPatientFixtures;
    private $addressFixtures;
    private $contactFixtures;
    private $contactAddressFixtures;

    /**
     * @var mixed
     */
    private $fhirAllergyIntoleranceFixtures;

    public function __construct()
    {
        $this->addressFixtures = $this->loadPhpFile("addresses.php");
        $this->contactAddressFixtures = $this->loadPhpFile("contact-addresses.php");
        $this->contactFixtures = $this->loadPhpFile("contacts.php");
        $this->patientFixtures = $this->loadPhpFile("patients.php");
        $this->fhirPatientFixtures = $this->loadJsonFile("FHIR/patients.json");
    }

    /**
     * @return array<string, mixed>[]
     */
    private function loadPhpFile(string $fileName): array
    {
        $filePath = realpath(__DIR__ . '/' . $fileName);
        if ($filePath === false || !str_starts_with($filePath, __DIR__ . '/')) {
            throw new \RuntimeException('Fixture file not found or outside fixtures directory: ' . $fileName);
        }
        /** @var array<string, mixed>[] */
        return require $filePath;
    }

    /**
     * Load a JSON fixture file. Used for FHIR fixtures that stay in JSON format.
     *
     * @return array<string, mixed>[]
     */
    private function loadJsonFile(string $fileName): array
    {
        /** @var array<string, mixed>[] $parsedRecords */
        $parsedRecords = json_decode((string) file_get_contents(__DIR__ . '/' . $fileName), true);
        return $parsedRecords;
    }

    /**
     *
     * This will return a recorded uuid (recorded in uuid_registry)
     *
     * @param $tableName The target OpenEMR DB table name.
     * @return string uuid
     */
    private function getUuid($tableName)
    {
        return (new UuidRegistry(['table_name' => $tableName]))->createUuid();
    }

    /**
     * @return int the next available patient pid/identifier.
     */
    private function getNextPid()
    {
        $pidQuery = "SELECT IFNULL(MAX(pid), 0) + 1 FROM patient_data";
        $pidResult = sqlQueryNoLog($pidQuery);
        $pidValue = intval(array_values($pidResult)[0]);
        return $pidValue;
    }

    /**
     * Installs fixtures into the OpenEMR DB.
     *
     * @param $tableName The target OpenEMR DB table name.
     * @param $fixtures Array of fixture objects to install.
     * @return int the number of fixtures installed.
     */
    private function installFixtures($tableName, $fixtures)
    {
        $insertCount = 0;
        $sqlInsert = "INSERT INTO " . escape_table_name($tableName) . " SET ";

        foreach ($fixtures as $fixture) {
            $sqlColumnValues = "";
            $sqlBinds = [];

            foreach ($fixture as $field => $fieldValue) {
                // not sure I like table specific comparisons here...
                if ($tableName == 'lists' && $field == 'pid') {
                    $sqlColumnValues .= "pid = (SELECT pid FROM patient_data WHERE pubpid=? LIMIT 1) ,";
                } else {
                    $sqlColumnValues .= $field . " = ?, ";
                }
//                if ($field === 'uuid') {
//                    $fieldValue = UuidRegistry::uuidToBytes($fieldValue);
//                }
                array_push($sqlBinds, $fieldValue);
            }

            if ($tableName == "patient_data") {
                // add pid
                $sqlColumnValues .= 'pid = ? ,';
                $nextPidValue = $this->getNextPid();
                array_push($sqlBinds, $nextPidValue);
                // add uuid
                $sqlColumnValues .= '`uuid` = ?';
                $uuidPatient = $this->getUuid("patient_data");
                array_push($sqlBinds, $uuidPatient);
            }
            $sqlColumnValues = rtrim($sqlColumnValues, " ,");

            $isInserted = QueryUtils::sqlInsert($sqlInsert . $sqlColumnValues, $sqlBinds);
            if ($isInserted) {
                $insertCount += 1;
            }
        }
        return $insertCount;
    }

    /**
     * @return array of fhir patient fixtures.
     */
    public function getFhirPatientFixtures()
    {
        return $this->fhirPatientFixtures;
    }

    /**
     * @return array<string, mixed> a single random fhir patient fixture
     */
    public function getSingleFhirPatientFixture()
    {
        return $this->getSingleEntry($this->fhirPatientFixtures);
    }

    /**
     * @return array of patient fixtures.
     */
    public function getPatientFixtures()
    {
        return $this->patientFixtures;
    }

    public function getAllergyIntoleranceFixtures()
    {
        if (empty($this->fhirAllergyIntoleranceFixtures)) {
            $this->fhirAllergyIntoleranceFixtures = $this->loadPhpFile("allergy-intolerance.php");
        }
        return $this->fhirAllergyIntoleranceFixtures;
    }

    /**
     * @return array of FHIR AllergyIntolerance fixtures.
     */
    public function getFhirAllergyIntoleranceFixtures()
    {
        return $this->loadJsonFile("FHIR/allergy-intolerance.json");
    }

    /**
     * @return mixed single/random fhir allergy intolerance fixture
     */
    public function getSingleFhirAllergyIntoleranceFixture()
    {
        return $this->getSingleEntry($this->getFhirAllergyIntoleranceFixtures());
    }

    /**
     * @return array of FHIR Immunization fixtures.
     */
    public function getFhirImmunizationFixtures()
    {
        return $this->loadJsonFile("FHIR/immunization.json");
    }

    /**
     * @return mixed single/random fhir immunization fixture
     */
    public function getSingleFhirImmunizationFixture()
    {
        return $this->getSingleEntry($this->getFhirImmunizationFixtures());
    }

    /**
     * @return array of FHIR Appointment fixtures.
     */
    public function getFhirAppointmentFixtures()
    {
        return $this->loadJsonFile("FHIR/appointment.json");
    }

    /**
     * @return mixed single/random fhir appointment fixture
     */
    public function getSingleFhirAppointmentFixture()
    {
        return $this->getSingleEntry($this->getFhirAppointmentFixtures());
    }

    /**
     * @return array<int, array<string, mixed>> FHIR ServiceRequest fixtures.
     */
    public function getFhirServiceRequestFixtures(): array
    {
        return $this->loadJsonFile("FHIR/service-request.json");
    }

    /**
     * @return mixed single/random fhir ServiceRequest fixture
     */
    public function getSingleFhirServiceRequestFixture()
    {
        return $this->getSingleEntry($this->getFhirServiceRequestFixtures());
    }

    /**
     * Removes procedure_order + procedure_order_code rows for test-fixture patients.
     */
    public function removeServiceRequestFixtures(): void
    {
        $pubpid = self::PATIENT_FIXTURE_PUBPID_PREFIX . "%";
        $pids = QueryUtils::fetchTableColumn(
            "SELECT pid FROM patient_data WHERE pubpid LIKE ?",
            'pid',
            [$pubpid]
        );
        if ($pids === []) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        $orderIds = QueryUtils::fetchTableColumn(
            "SELECT procedure_order_id FROM procedure_order WHERE patient_id IN ($placeholders)",
            'procedure_order_id',
            $pids
        );
        if ($orderIds !== []) {
            $oplaceholders = implode(',', array_fill(0, count($orderIds), '?'));
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_order_code WHERE procedure_order_id IN ($oplaceholders)",
                $orderIds
            );
        }
        $orderUuids = QueryUtils::fetchTableColumn(
            "SELECT uuid FROM procedure_order WHERE patient_id IN ($placeholders)",
            'uuid',
            $pids
        );
        foreach ($orderUuids as $bytes) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM uuid_registry WHERE table_name = 'procedure_order' AND uuid = ?",
                [$bytes]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM procedure_order WHERE patient_id IN ($placeholders)",
            $pids
        );
    }

    /**
     * @return array<int, array<string, mixed>> FHIR PractitionerRole fixtures.
     */
    public function getFhirPractitionerRoleFixtures(): array
    {
        return $this->loadJsonFile("FHIR/practitioner-role.json");
    }

    /**
     * @return mixed single/random fhir PractitionerRole fixture
     */
    public function getSingleFhirPractitionerRoleFixture()
    {
        return $this->getSingleEntry($this->getFhirPractitionerRoleFixtures());
    }

    /**
     * Removes facility_user_ids rows referencing test-fixture users. The Practitioner
     * fixture cleanup (PractitionerFixtureManager::removePractitionerFixtures) DELETEs
     * the users rows; we delete the role rows here before that to avoid orphans.
     */
    public function removePractitionerRoleFixtures(): void
    {
        $userIds = QueryUtils::fetchTableColumn(
            "SELECT id FROM users WHERE fname LIKE 'test-fixture-%'",
            'id',
            []
        );
        if ($userIds === []) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $roleUuids = QueryUtils::fetchTableColumn(
            "SELECT uuid FROM facility_user_ids WHERE uid IN ($placeholders)",
            'uuid',
            $userIds
        );
        foreach ($roleUuids as $bytes) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM uuid_registry WHERE table_name = 'facility_user_ids' AND uuid = ?",
                [$bytes]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM facility_user_ids WHERE uid IN ($placeholders)",
            $userIds
        );
    }

    /**
     * @return array<int, array<string, mixed>> FHIR RelatedPerson fixtures.
     */
    public function getFhirRelatedPersonFixtures(): array
    {
        return $this->loadJsonFile("FHIR/related-person.json");
    }

    /**
     * @return mixed single/random fhir RelatedPerson fixture
     */
    public function getSingleFhirRelatedPersonFixture()
    {
        return $this->getSingleEntry($this->getFhirRelatedPersonFixtures());
    }

    /**
     * Removes the multi-table footprint of RelatedPerson test fixtures: contact_relation
     * rows pointing at the test-fixture persons, those persons' contact rows + telecoms +
     * addresses, the addresses themselves, and finally the person rows.
     */
    public function removeRelatedPersonFixtures(): void
    {
        $personIds = QueryUtils::fetchTableColumn(
            "SELECT id FROM person WHERE first_name LIKE 'test-fixture-%' OR last_name LIKE 'test-fixture-%'",
            'id',
            []
        );
        if ($personIds === []) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($personIds), '?'));

        $contactIds = QueryUtils::fetchTableColumn(
            "SELECT id FROM contact WHERE foreign_table_name = 'person' AND foreign_id IN ($placeholders)",
            'id',
            $personIds
        );

        QueryUtils::sqlStatementThrowException(
            "DELETE FROM contact_relation WHERE target_table = 'person' AND target_id IN ($placeholders)",
            $personIds
        );

        if ($contactIds !== []) {
            $cplaceholders = implode(',', array_fill(0, count($contactIds), '?'));
            $addressIds = QueryUtils::fetchTableColumn(
                "SELECT address_id FROM contact_address WHERE contact_id IN ($cplaceholders)",
                'address_id',
                $contactIds
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM contact_telecom WHERE contact_id IN ($cplaceholders)",
                $contactIds
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM contact_address WHERE contact_id IN ($cplaceholders)",
                $contactIds
            );
            if ($addressIds !== []) {
                $aplaceholders = implode(',', array_fill(0, count($addressIds), '?'));
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM addresses WHERE id IN ($aplaceholders)",
                    $addressIds
                );
            }
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM contact WHERE id IN ($cplaceholders)",
                $contactIds
            );
        }

        $personUuidBytes = QueryUtils::fetchTableColumn(
            "SELECT uuid FROM person WHERE id IN ($placeholders)",
            'uuid',
            $personIds
        );
        foreach ($personUuidBytes as $bytes) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM uuid_registry WHERE table_name = 'person' AND uuid = ?",
                [$bytes]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM person WHERE id IN ($placeholders)",
            $personIds
        );
    }

    /**
     * @return array<int, array<string, mixed>> FHIR Device fixtures.
     */
    public function getFhirDeviceFixtures(): array
    {
        return $this->loadJsonFile("FHIR/device.json");
    }

    /**
     * @return mixed single/random fhir Device fixture
     */
    public function getSingleFhirDeviceFixture()
    {
        return $this->getSingleEntry($this->getFhirDeviceFixtures());
    }

    /**
     * Removes medical_device rows in `lists` for test-fixture patients. Scoped to
     * type='medical_device' so this doesn't affect other list types (allergies,
     * problems, medications) that share the table.
     */
    public function removeDeviceFixtures(): void
    {
        $pubpid = self::PATIENT_FIXTURE_PUBPID_PREFIX . "%";
        $pids = QueryUtils::fetchTableColumn(
            "SELECT pid FROM patient_data WHERE pubpid LIKE ?",
            'pid',
            [$pubpid]
        );
        if ($pids === []) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        $uuids = QueryUtils::fetchTableColumn(
            "SELECT uuid FROM lists WHERE type = 'medical_device' AND pid IN ($placeholders)",
            'uuid',
            $pids
        );
        foreach ($uuids as $bytes) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM uuid_registry WHERE table_name = 'lists' AND uuid = ?",
                [$bytes]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM lists WHERE type = 'medical_device' AND pid IN ($placeholders)",
            $pids
        );
    }

    /**
     * @return array<int, array<string, mixed>> FHIR Medication fixtures.
     */
    public function getFhirMedicationFixtures(): array
    {
        return $this->loadJsonFile("FHIR/medication.json");
    }

    /**
     * @return mixed single/random fhir Medication fixture
     */
    public function getSingleFhirMedicationFixture()
    {
        return $this->getSingleEntry($this->getFhirMedicationFixtures());
    }

    /**
     * Removes any `drugs` row whose name matches the test-fixture prefix. Drug uuid
     * rows in uuid_registry are removed first.
     */
    public function removeMedicationFixtures(): void
    {
        $bytesList = QueryUtils::fetchTableColumn(
            "SELECT uuid FROM drugs WHERE name LIKE ?",
            'uuid',
            ['test-fixture-%']
        );
        foreach ($bytesList as $bytes) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM uuid_registry WHERE table_name = 'drugs' AND uuid = ?",
                [$bytes]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM drugs WHERE name LIKE ?",
            ['test-fixture-%']
        );
    }

    /**
     * @return array<int, array<string, mixed>> FHIR Person fixtures.
     */
    public function getFhirPersonFixtures(): array
    {
        return $this->loadJsonFile("FHIR/persons.json");
    }

    /**
     * @return mixed single/random fhir Person fixture
     */
    public function getSingleFhirPersonFixture()
    {
        return $this->getSingleEntry($this->getFhirPersonFixtures());
    }

    /**
     * @return array<int, array<string, mixed>> FHIR CarePlan fixtures.
     */
    public function getFhirCarePlanFixtures(): array
    {
        return $this->loadJsonFile("FHIR/care-plan.json");
    }

    /**
     * @return mixed single/random fhir care plan fixture
     */
    public function getSingleFhirCarePlanFixture()
    {
        return $this->getSingleEntry($this->getFhirCarePlanFixtures());
    }

    /**
     * Removes care_plan forms and their form_care_plan rows for test-fixture patients.
     * Encounter rows are cleaned up by FhirEncounterServiceCrudTest's tearDown convention
     * (DELETE FROM form_encounter WHERE reason LIKE 'test-fixture%').
     */
    public function removeCarePlanFixtures(): void
    {
        $pubpid = self::PATIENT_FIXTURE_PUBPID_PREFIX . "%";
        $pids = QueryUtils::fetchTableColumn(
            "SELECT `pid` FROM `patient_data` WHERE `pubpid` LIKE ?",
            'pid',
            [$pubpid]
        );
        if (empty($pids)) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM form_care_plan WHERE pid IN ($placeholders)",
            $pids
        );
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM forms WHERE pid IN ($placeholders) AND formdir = 'care_plan'",
            $pids
        );
    }

    /**
     * @return array<int, array<string, mixed>> FHIR MedicationRequest fixtures.
     */
    public function getFhirMedicationRequestFixtures(): array
    {
        return $this->loadJsonFile("FHIR/medication-request.json");
    }

    /**
     * @return mixed single/random fhir medication request fixture
     */
    public function getSingleFhirMedicationRequestFixture()
    {
        return $this->getSingleEntry($this->getFhirMedicationRequestFixtures());
    }

    public function removeMedicationRequestFixtures(): void
    {
        $pubpid = self::PATIENT_FIXTURE_PUBPID_PREFIX . "%";
        $pids = QueryUtils::fetchTableColumn(
            "SELECT `pid` FROM `patient_data` WHERE `pubpid` LIKE ?",
            'pid',
            [$pubpid]
        );
        if (empty($pids)) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        $uuids = QueryUtils::fetchTableColumn(
            "SELECT `uuid` FROM `prescriptions` WHERE `patient_id` IN ($placeholders)",
            'uuid',
            $pids
        );
        foreach ($uuids as $bytes) {
            sqlQuery(
                "DELETE FROM uuid_registry WHERE table_name = 'prescriptions' AND uuid = ?",
                [$bytes]
            );
        }
        sqlStatement(
            "DELETE FROM prescriptions WHERE patient_id IN ($placeholders)",
            $pids
        );
    }

    /**
     * @return array<int, array<string, mixed>> FHIR Coverage fixtures.
     */
    public function getFhirCoverageFixtures(): array
    {
        return $this->loadJsonFile("FHIR/coverage.json");
    }

    /**
     * @return mixed single/random fhir coverage fixture
     */
    public function getSingleFhirCoverageFixture()
    {
        return $this->getSingleEntry($this->getFhirCoverageFixtures());
    }

    /**
     * Insert a synthetic insurance company row so that Coverage write tests have a
     * payor reference to dereference. Returns the uuid string.
     */
    public function installInsuranceCompanyFixture(): string
    {
        $uuid = (new UuidRegistry(['table_name' => 'insurance_companies']))->createUuid();
        sqlInsert(
            "INSERT INTO insurance_companies (uuid, name, attn, cms_id, ins_type_code) "
            . "VALUES (?, ?, ?, ?, ?)",
            [$uuid, 'test-fixture-insurer', null, null, 3]
        );
        return UuidRegistry::uuidToString($uuid);
    }

    public function removeInsuranceCompanyFixtures(): void
    {
        $bytesList = QueryUtils::fetchTableColumn(
            "SELECT uuid FROM insurance_companies WHERE name LIKE ?",
            'uuid',
            ['test-fixture-%']
        );
        foreach ($bytesList as $bytes) {
            sqlQuery(
                "DELETE FROM uuid_registry WHERE table_name = 'insurance_companies' AND uuid = ?",
                [$bytes]
            );
        }
        sqlStatement("DELETE FROM insurance_companies WHERE name LIKE ?", ['test-fixture-%']);
    }

    public function removeCoverageFixtures(): void
    {
        $pubpid = self::PATIENT_FIXTURE_PUBPID_PREFIX . "%";
        $pids = QueryUtils::fetchTableColumn(
            "SELECT `pid` FROM `patient_data` WHERE `pubpid` LIKE ?",
            'pid',
            [$pubpid]
        );
        if (empty($pids)) {
            return;
        }
        // delete uuid_registry rows for insurance_data referenced by these pids
        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        $uuids = QueryUtils::fetchTableColumn(
            "SELECT `uuid` FROM `insurance_data` WHERE `pid` IN ($placeholders)",
            'uuid',
            $pids
        );
        foreach ($uuids as $bytes) {
            sqlQuery(
                "DELETE FROM uuid_registry WHERE table_name = 'insurance_data' AND uuid = ?",
                [$bytes]
            );
        }
        sqlStatement(
            "DELETE FROM insurance_data WHERE pid IN ($placeholders)",
            $pids
        );
    }

    /**
     * @template T
     * @param T[] $array
     * @return T
     */
    private function getSingleEntry(array $array): mixed
    {
        $randomIndex = array_rand($array, 1);
        return $array[$randomIndex];
    }

    /**
     * @return array<string, mixed>
     */
    public function getSinglePatientFixture(): array
    {
        return $this->getSingleEntry($this->patientFixtures);
    }

    public function getSinglePatientFixtureWithAddressInformation()
    {
        $entry = $this->getSingleEntry($this->patientFixtures);
        $address = $this->getSingleEntry($this->addressFixtures);
        $contactAddress = $this->getSingleEntry($this->contactAddressFixtures);
        $contactAddress['contact_id'] = hexdec(uniqid()); // just need a random unique id
        unset($contactAddress['address_id']);

        // now combine our contact address and address for our unique address information
        $entry['addresses'] = [
            array_merge($address, $contactAddress)
        ];
        return $entry;
    }

    /**
     * Installs Patient Fixtures into the OpenEMR DB.
     */
    public function installPatientFixtures()
    {
        return $this->installFixtures("patient_data", $this->getPatientFixtures());
    }

    /**
     * Installs a single Patient Fixtures into the OpenEMR DB.
     * @param $patientFixture - The fixture to install.
     * @return int the number of records inserted.
     */
    public function installSinglePatientFixture($patientFixture)
    {
        return $this->installFixtures("patient_data", [$patientFixture]);
    }

    /**
     * Removes Patient Fixtures from the OpenEMR DB.
     */
    public function removePatientFixtures()
    {
        $bindVariable = self::PATIENT_FIXTURE_PUBPID_PREFIX . "%";

        // remove the related uuids from uuid_registry
        $select = "SELECT `uuid` FROM `patient_data` WHERE `pubpid` LIKE ?";
        $sel = sqlStatement($select, [$bindVariable]);
        while ($row = sqlFetchArray($sel)) {
            sqlQuery("DELETE FROM `uuid_registry` WHERE `table_name` = 'patient_data' AND `uuid` = ?", [$row['uuid']]);
        }

        // remove the patients
        $delete = "DELETE FROM patient_data WHERE pubpid LIKE ?";
        sqlStatement($delete, [$bindVariable]);
    }

    public function installAllergyIntoleranceFixtures()
    {
        $this->installPatientFixtures();
        $installed = $this->installFixtures("lists", $this->getAllergyIntoleranceFixtures());
        if ($installed < 1) {
            throw new \RuntimeException("Failed to install allergy intolerance fixtures");
        }
    }

    public function removeAllergyIntoleranceFixtures()
    {
        $pubpid = self::PATIENT_FIXTURE_PUBPID_PREFIX . "%";
        $pids = QueryUtils::fetchTableColumn(
            "SELECT `pid` FROM `patient_data` WHERE `pubpid` LIKE ?",
            'pid',
            [$pubpid]
        );

        if (!empty($pids)) {
            $count = count($pids) - 1;
            $where = "WHERE pid = ? " . str_repeat("OR pid = ? ", $count);
            $sqlStatement = "DELETE FROM `lists` " . $where;
            QueryUtils::sqlStatementThrowException($sqlStatement, $pids);
        }
        $this->removePatientFixtures();
    }

    /**
     * Returns an unregistered/unlogged UUID for use in testing fixtures
     * @return string a uuid4 string value
     */
    public function getUnregisteredUuid()
    {
        $uuid4 = Uuid::uuid4();
        return $uuid4->toString();
    }
}
