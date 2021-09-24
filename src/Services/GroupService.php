<?php

/**
 * GroupService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;

class GroupService extends BaseService
{
    const PRACTITIONER_TABLE = "users";

    /**
     * Default constructor.
     */
    public function __construct()
    {
        UuidRegistry::createMissingUuidsForTables([self::PRACTITIONER_TABLE]);
        UuidMapping::createMissingResourceUuids("Group", self::PRACTITIONER_TABLE);
        parent::__construct(self::PRACTITIONER_TABLE);
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid'];
    }

    /**
     * Searches for patient provider groups
     * @param array $search
     * @param bool $isAndCondition
     * @return ProcessingResult
     */
    public function searchPatientProviderGroups($search = array(), $isAndCondition = true)
    {
        // we inner join on status in case we ever decide to add a status property (and layers above this one can rely
        // on the property without changing code).
        $sql = "SELECT
                    patient_provider_groups.uuid
                    ,patient_provider_groups.provider_id
                    ,patient_provider_groups.provider_fname
                    ,patient_provider_groups.provider_mname
                    ,patient_provider_groups.provider_lname
                    ,patient_provider_groups.puuid
                    ,patient_provider_groups.patient_title
                    ,patient_provider_groups.patient_fname
                    ,patient_provider_groups.patient_mname
                    ,patient_provider_groups.patient_lname
                FROM (
                    SELECT
                        uuid_mapping.target_uuid AS pruuid
                        ,uuid_mapping.uuid
                        ,users.id AS provider_id
                        ,users.fname AS provider_fname
                        ,users.lname AS provider_lname
                        ,users.mname AS provider_mname
                        ,patients.uuid AS puuid
                        ,patients.title AS patient_title
                        ,patients.fname AS patient_fname
                        ,patients.mname AS patient_mname
                        ,patients.lname AS patient_lname
                    FROM
                        uuid_mapping
                    JOIN
                        users ON uuid_mapping.target_uuid = users.uuid
                    LEFT JOIN patient_data AS patients ON patients.providerID = users.id
                    WHERE users.npi IS NOT NULL and users.npi != '' AND uuid_mapping.resource = 'Group'
                ) patient_provider_groups ";

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = $this->hydratePatientProviderSearchResultsFromQueryResource($statementResults);
        return $processingResult;
    }

    private function hydratePatientProviderSearchResultsFromQueryResource($queryResource)
    {
        $processingResult = new ProcessingResult();
        $recordsByUuid = [];
        $recordFields = array_combine($this->getFields(), $this->getFields());
        $previousNameColumns = ['previous_name_prefix', 'previous_name_first', 'previous_name_middle'
            , 'previous_name_last', 'previous_name_suffix', 'previous_name_enddate'];
        $previousNamesFields = array_combine($previousNameColumns, $previousNameColumns);
        $orderedList = [];
        while ($row = sqlFetchArray($queryResource)) {
            $dbRecord = $this->createResultRecordFromDatabaseResult($row);
            $recordUuid = $dbRecord['uuid'];
            if (!isset($recordsByUuid[$recordUuid])) {
                $providerName = $dbRecord['provider_fname'] ?? "";
                $providerName .= !empty($dbRecord['provider_mname']) ? " " . $dbRecord['provider_mname'] : "";
                $providerName .= !empty($dbRecord['provider_lname']) ? " " . $dbRecord['provider_lname' ] : "";

                if (empty($providerName)) {
                    $providerName = "(Provider Name Unknown)";
                }
                $groupName = $providerName . " " . xl("Patients");
                $record = [
                    'uuid' => $recordUuid
                    ,'name' => $groupName
                    ,'patients' => []
                ];
                $orderedList[] = $recordUuid;
            } else {
                $record = $recordsByUuid[$recordUuid];
            }
            if (!empty($dbRecord['puuid'])) {
                $patientName = $dbRecord['patient_title'] ?? "";
                $patientName .= !empty($dbRecord['patient_fname']) ? " " . $dbRecord['patient_fname'] : "";
                $patientName .= !empty($dbRecord['patient_mname']) ? " " . $dbRecord['patient_mname'] : "";
                $patientName .= !empty($dbRecord['patient_lname']) ? " " . $dbRecord['patient_lname'] : "";
                $record['patients'][] = [
                    'uuid' => $dbRecord['puuid']
                    ,'name' => $patientName
                ];
            }

            // now let's grab our history
            $recordsByUuid[$recordUuid] = $record;
        }
        foreach ($orderedList as $uuid) {
            $patient = $recordsByUuid[$uuid];
            $processingResult->addData($patient);
        }
        return $processingResult;
    }
}
