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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Common\Uuid\UuidMapping;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
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

    public function search($search, $isAndCondition = true)
    {
        // we inner join on status in case we ever decide to add a status property (and layers above this one can rely
        // on the property without changing code).
        $sql = "SELECT 
                    careteam_mapping.puuid,
                    careteam_mapping.uuid,
                    careteam_mapping.care_team_provider as providers,
                    careteam_mapping.care_team_facility as facilities,
                    careteamStatus.careteam_status
                FROM (
                    SELECT 
                        uuid_mapping.target_uuid AS puuid
                        ,uuid_mapping.uuid
                        ,patient_data.care_team_provider
                        ,patient_data.care_team_facility
                    FROM 
                        uuid_mapping
                    -- we join on this to make sure we've got data integrity since we don't actually use foreign keys right now
                    JOIN 
                        patient_data ON uuid_mapping.target_uuid = patient_data.uuid
                    WHERE 
                        uuid_mapping.resource='CareTeam'
                ) careteam_mapping
                CROSS JOIN (
                    select 'active' AS careteam_status
                ) careteamStatus";

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }
        return $processingResult;
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

        $newSearch = [];
        foreach ($search as $key => $value) {
            if (!$value instanceof ISearchField) {
                $newSearch[] = new StringSearchField($key, [$value], SearchModifier::EXACT);
            } else {
                $newSearch[$key] = $value;
            }
        }

        // override puuid, this replaces anything in search if it is already specified.
        if (isset($puuidBind)) {
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }

        return $this->search($search, $isAndCondition);
    }

    public function createResultRecordFromDatabaseResult($row)
    {
        $row['providers'] = $this->getProvidersWithType($row['providers']);
        $row['facilities'] = $this->getFacilities($row['facilities']);
        $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
        $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
        return $row;
    }

    private function getFacilities($facilities)
    {
        $facilityIds = explode(",", $facilities);
        $service = new FacilityService();
        $result = $service->getAllWithIds($facilityIds);
        $providers = $result->getData() ?? [];
        return $providers;
    }

    private function getProvidersWithType($providers)
    {
        $providers = explode("|", $providers);

        $practitionerRoleService = new PractitionerRoleService();
        $result = $practitionerRoleService->getAllByPractitioners($providers);

        $providers = $result->getData() ?? [];
        return $providers;
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


        if (isset($puuidBind)) {
            $search['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }

        $search['uuid'] = new TokenSearchField('uuid', $uuid, true);

        return $this->search($search);
    }
}
