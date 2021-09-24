<?php

/**
 * DeviceService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
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
                    `uuid`, `date`, `title`,`udi_data`, `begdate`, `diagnosis`, `user`, `pid`
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
            $dataSet = json_decode($json, JSON_THROW_ON_ERROR);
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
            (new SystemLogger())->error(self::class . "->createResultRecordFromDatabaseResult() failed to decode udi_data json ", ['message' => $error->getMessage(), 'trace' => $error->getTrace()]);
        }
        return $record;
    }
}
