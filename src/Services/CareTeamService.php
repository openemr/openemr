<?php

/**
 * CareTeamService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class CareTeamService extends BaseService
{

    private const PATIENT_TABLE = "patient_data";
    private const PRACTITIONER_TABLE = "users";
    private const FACILITY_TABLE = "facility";
    private $uuidRegistry;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->uuidMapping = new UuidMapping();
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PRACTITIONER_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::FACILITY_TABLE]))->createMissingUuids();
        $this->uuidMapping->createMissingResourceUuids("CareTeam", self::PATIENT_TABLE);
    }

    /**
     * Returns a list of careTeams matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true)
    {
        $sqlBindArray = array();

        $sql = "SELECT careTeam.*, uuid_mapping.uuid as uuid FROM 
                (SELECT patient.uuid as puuid,
                users.fname as prac_name,
                users.uuid as pruuid,
                facility.name as fac_name,
                facility.uuid as ouuid
                FROM patient_data as patient
                LEFT JOIN users ON users.id=patient.care_team_provider
                LEFT JOIN facility ON facility.id=patient.care_team_facility) as careTeam
                LEFT JOIN uuid_mapping ON uuid_mapping.target_uuid=careTeam.puuid AND uuid_mapping.resource='CareTeam'";

        if (!empty($search)) {
            $sql .= ' WHERE ';
            $whereClauses = array();
            foreach ($search as $fieldName => $fieldValue) {
                array_push($whereClauses, $fieldName . ' = ?');
                array_push($sqlBindArray, $fieldValue);
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
        }

        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
            $row['pruuid'] = $row['pruuid'] ? UuidRegistry::uuidToString($row['pruuid']) : null;
            $row['ouuid'] = $row['ouuid'] ? UuidRegistry::uuidToString($row['ouuid']) : null;
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Returns a single careTeam record by id.
     * @param $uuid - The careTeam uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = BaseValidator::validateId(
            "uuid",
            "uuid_mapping",
            $uuid,
            true
        );

        $sql = "SELECT careTeam.*, uuid_mapping.uuid as uuid FROM 
                (SELECT patient.uuid as puuid,
                users.fname as prac_name,
                users.uuid as pruuid,
                facility.name as fac_name,
                facility.uuid as ouuid
                FROM patient_data as patient
                LEFT JOIN users ON users.id=patient.care_team_provider
                LEFT JOIN facility ON facility.id=patient.care_team_facility) as careTeam
                LEFT JOIN uuid_mapping ON uuid_mapping.target_uuid=careTeam.puuid AND uuid_mapping.resource='CareTeam'
                WHERE uuid_mapping.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
        $sqlResult['pruuid'] = UuidRegistry::uuidToString($sqlResult['pruuid']);
        $sqlResult['ouuid'] = UuidRegistry::uuidToString($sqlResult['ouuid']);
        $processingResult->addData($sqlResult);
        return $processingResult;
    }
}
