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

    /**
     * Default constructor.
     */
    public function __construct()
    {
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PRACTITIONER_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::FACILITY_TABLE]))->createMissingUuids();
        UuidMapping::createMissingResourceUuids("CareTeam", self::PATIENT_TABLE);
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

        $sql = "SELECT patient.uuid as puuid,
                patient.care_team_provider as providers,
                patient.care_team_facility as facilities,
                uuid_mapping.uuid as uuid
                FROM patient_data as patient
                LEFT JOIN uuid_mapping ON uuid_mapping.target_uuid=patient.uuid AND uuid_mapping.resource='CareTeam'";

        $statementResults = sqlStatement($sql);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['providers'] = $this->splitAndProcessMultipleFields($row['providers'], "users");
            $row['facilities'] = $this->splitAndProcessMultipleFields($row['facilities'], "facility");
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
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

        $sql = "SELECT patient.uuid as puuid,
                patient.care_team_provider as providers,
                patient.care_team_facility as facilities,
                uuid_mapping.uuid as uuid
                FROM patient_data as patient
                LEFT JOIN uuid_mapping ON uuid_mapping.target_uuid=patient.uuid AND uuid_mapping.resource='CareTeam'
                WHERE uuid_mapping.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
        $sqlResult['providers'] = $this->splitAndProcessMultipleFields($sqlResult['providers'], "users");
        $sqlResult['facilities'] = $this->splitAndProcessMultipleFields($sqlResult['facilities'], "facility");
        $processingResult->addData($sqlResult);
        return $processingResult;
    }
}
