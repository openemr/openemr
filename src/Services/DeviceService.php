<?php

/**
 * DeviceService.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class DeviceService extends BaseService
{
    private const DEVICE_TABLE = "lists";
    private const PATIENT_TABLE = "patient_data";

    public function __construct()
    {
        parent::__construct(self::DEVICE_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::DEVICE_TABLE, self::PATIENT_TABLE]);
    }

    public function search($search, $isAndCondition = true)
    {
        $sql = "
            select l.*
            , patients.*
            , provider.*
            from
            (
                SELECT
                    `udi`,
                    `uuid`, `date`, `title`,`udi_data`, `begdate`, `diagnosis`, `user`, `pid`,modifydate
                FROM lists WHERE `type` = 'medical_device'
            ) l
            JOIN (
                SELECT `pid`,`uuid` AS `puuid`
                from patient_data
            ) patients ON l.pid = patients.pid
            LEFT JOIN (
                select
                   id AS provider_id
                   ,npi AS provider_npi
                   ,uuid AS provider_uuid
                   ,username as provider_username
                FROM users
            ) provider ON l.user = provider.provider_username";

        $search = is_array($search) ? $search : [];

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = QueryUtils::fetchArrayFromResultSet($statementResults)) {
            $record = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($record);
        }
        return $processingResult;
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid'];
    }

    /**
     * Inserts a new medical_device entry in the `lists` table.
     *
     * Fields expected on $data:
     *   - pid (int, required) — patient internal id
     *   - title (string, optional) — display label
     *   - diagnosis (string, optional) — SNOMED-prefixed code (e.g. SNOMED-CT:49062001)
     *   - udi (string, optional) — UDI carrier HRF
     *   - udi_data (array, optional) — assembled JSON blob for standard_elements
     *   - begdate (string|null, optional)
     *   - enddate (string|null, optional)
     *   - user (string, optional) — username of recording provider
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): ProcessingResult
    {
        $result = new ProcessingResult();

        if (!isset($data['pid']) || !is_numeric($data['pid']) || (int) $data['pid'] <= 0) {
            $result->setValidationMessages(['patient' => 'A resolvable patient reference is required']);
            return $result;
        }

        $data['type'] = 'medical_device';
        $data['activity'] ??= 1;
        if (isset($data['udi_data']) && is_array($data['udi_data'])) {
            $data['udi_data'] = json_encode($data['udi_data']);
        }
        if (empty($data['date'])) {
            $data['date'] = date('Y-m-d H:i:s');
        }

        $data['uuid'] = (new UuidRegistry(['table_name' => self::DEVICE_TABLE]))->createUuid();

        $query = $this->buildInsertColumns($data);

        /** @var string $setClause */
        $setClause = $query['set'];
        /** @var array<int, mixed> $binds */
        $binds = $query['bind'];

        $sql = "INSERT INTO " . self::DEVICE_TABLE . " SET " . $setClause;
        $newId = QueryUtils::sqlInsert($sql, $binds);

        if ($newId) {
            $result->addData([
                'id' => $newId,
                'uuid' => UuidRegistry::uuidToString($data['uuid']),
            ]);
        } else {
            $result->addInternalError("error processing SQL Insert");
        }

        return $result;
    }

    /**
     * Updates an existing medical_device entry, scoped to type='medical_device' to avoid
     * accidentally mutating other list types that share the `lists` table.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $uuid, array $data): ProcessingResult
    {
        $isValid = BaseValidator::validateId('uuid', self::DEVICE_TABLE, $uuid, true);
        if ($isValid instanceof ProcessingResult) {
            return $isValid;
        }

        if ($data === []) {
            $result = new ProcessingResult();
            $result->setValidationMessages(['data' => 'No update fields supplied']);
            return $result;
        }

        unset($data['uuid'], $data['type']);

        if (isset($data['udi_data']) && is_array($data['udi_data'])) {
            $data['udi_data'] = json_encode($data['udi_data']);
        }
        $data['modifydate'] = date('Y-m-d H:i:s');

        $query = $this->buildUpdateColumns($data);

        /** @var string $setClause */
        $setClause = $query['set'];
        /** @var array<int, mixed> $binds */
        $binds = $query['bind'];

        if ($setClause === '') {
            $result = new ProcessingResult();
            $result->setValidationMessages(['data' => 'No recognized fields to update']);
            return $result;
        }

        $sql = "UPDATE " . self::DEVICE_TABLE . " SET " . $setClause
            . " WHERE uuid = ? AND type = 'medical_device'";
        $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        $binds[] = $uuidBytes;
        QueryUtils::sqlStatementThrowException($sql, $binds);

        // Build a minimal record from the post-update row so callers see the result.
        // (The full read-side search query joins patient_data + users; we don't need
        // all of that just to confirm success.)
        $result = new ProcessingResult();
        $row = QueryUtils::querySingleRow(
            "SELECT uuid, pid, title, udi, udi_data, date, modifydate "
            . "FROM lists WHERE uuid = ? AND type = 'medical_device'",
            [$uuidBytes]
        );
        if (is_array($row)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $result->addData($row);
        }
        return $result;
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        // handle any uuids
        $record = parent::createResultRecordFromDatabaseResult($row);

        $json = $record['udi_data'] ?? '{}';
        if (!empty($record['diagnosis'])) {
            $record['code'] = $this->addCoding($record['diagnosis']);
            $record['code_full'] = $record['diagnosis'];
        }

        try {
            $dataSet = json_decode((string) $json, JSON_THROW_ON_ERROR);
            $standardElements = $dataSet['standard_elements'] ?? [];
            unset($record['udi_data']); // don't send back the JSON array
            $record['udi_di'] = $standardElements['di'] ?? null;
            $record['manufacturer'] = $standardElements['companyName'] ?? null;
            $record['manufactureDate'] = $standardElements['manufacturingDate'] ?? null;
            $record['expirationDate'] = $standardElements['expirationDate'] ?? null;
            $record['lotNumber'] = $standardElements['lotNumber'] ?? null;
            $record['serialNumber'] = $standardElements['serialNumber'] ?? null;
            // @see https://www.accessdata.fda.gov/scripts/cdrh/cfdocs/cfcfr/CFRSearch.cfm?fr=1271.290 which describes
            // the distinct identification code which states this is the donor id.
            $record['distinctIdentifier'] = $standardElements['donationId'] ?? null;
        } catch (\JsonException $error) {
            ServiceContainer::getLogger()->error(self::class . "->createResultRecordFromDatabaseResult() failed to decode udi_data json ", ['message' => $error->getMessage(), 'trace' => $error->getTrace()]);
        }
        return $record;
    }
}
