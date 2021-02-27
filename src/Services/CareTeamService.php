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
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true, $puuidBind = null)
    {
        if (!empty($puuidBind)) {
            // code to support patient binding
            $isValidPatient = BaseValidator::validateId(
                'uuid',
                self::PATIENT_TABLE,
                $puuidBind,
                true
            );
            if ($isValidPatient !== true) {
                return $isValidPatient;
            }
        }

        $sqlBindArray = array();
        $sql = "SELECT patient.uuid as puuid,
                patient.care_team_provider as providers,
                patient.care_team_facility as facilities,
                uuid_mapping.uuid as uuid
                FROM patient_data as patient
                LEFT JOIN uuid_mapping ON uuid_mapping.target_uuid=patient.uuid AND uuid_mapping.resource='CareTeam'";

        if (!empty($puuidBind)) {
            // code to support patient binding
            $sql .= " WHERE `patient`.`uuid` = ?";
            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
        }

        $statementResults = sqlStatement($sql, $sqlBindArray);

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
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid, $puuidBind = null)
    {
        $processingResult = new ProcessingResult();

        $isValid = BaseValidator::validateId(
            "uuid",
            "uuid_mapping",
            $uuid,
            true
        );
        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        if (!empty($puuidBind)) {
            // code to support patient binding
            $isValid = BaseValidator::validateId(
                "uuid",
                self::PATIENT_TABLE,
                $puuidBind,
                true
            );
            if ($isValid !== true) {
                $validationMessages = [
                    'puuid' => ["invalid or nonexisting value" => " value " . $puuidBind,]
                ];
                $processingResult->setValidationMessages($validationMessages);
                return $processingResult;
            }
        }

        $sql = "SELECT patient.uuid as puuid,
                patient.care_team_provider as providers,
                patient.care_team_facility as facilities,
                uuid_mapping.uuid as uuid
                FROM patient_data as patient
                LEFT JOIN uuid_mapping ON uuid_mapping.target_uuid=patient.uuid AND uuid_mapping.resource='CareTeam'
                WHERE uuid_mapping.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlBindArray = [$uuidBinary];

        if (!empty($puuidBind)) {
            // code to support patient binding
            $sql .= " AND `patient`.`uuid` = ?";
            $sqlBindArray[] = UuidRegistry::uuidToBytes($puuidBind);
        }

        $sqlResult = sqlQuery($sql, $sqlBindArray);

        if (!empty($sqlResult)) {
            $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
            $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
            $sqlResult['providers'] = $this->splitAndProcessMultipleFields($sqlResult['providers'], "users");
            $sqlResult['facilities'] = $this->splitAndProcessMultipleFields($sqlResult['facilities'], "facility");
            $processingResult->addData($sqlResult);
        }
        return $processingResult;
    }
}
